<?php

namespace Tests\Feature\GameAuth;

use App\Accounts\Models\IdentityCanaryAccount;
use App\GameAuth\Tickets\GameLoginTicket;
use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\GameAuth\Tickets\RedeemGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;
use RuntimeException;
use Tests\TestCase;
use Throwable;

final class GameLoginTicketMariaDbConcurrencyTest extends TestCase
{
    private const CONNECTION = 'game_auth_concurrency';

    private ?PDO $root = null;

    private ?string $database = null;

    private ?string $temporaryDirectory = null;

    private string $originalDefaultConnection = 'sqlite';

    protected function setUp(): void
    {
        parent::setUp();

        $host = getenv('CANARY_PROVISIONING_INTEGRATION_HOST');

        if (! is_string($host) || $host === '') {
            $this->markTestSkipped('MariaDB concurrency integration environment is not configured.');
        }

        if (! function_exists('pcntl_fork') || ! function_exists('posix_kill')) {
            $this->markTestSkipped('The pcntl and posix extensions are required for concurrency validation.');
        }

        $port = getenv('CANARY_PROVISIONING_INTEGRATION_PORT');
        $rootPassword = getenv('CANARY_PROVISIONING_INTEGRATION_ROOT_PASSWORD');
        $port = is_string($port) && $port !== '' ? $port : '3306';
        $rootPassword = is_string($rootPassword) ? $rootPassword : '';
        $configuredDefault = config('database.default');
        $this->originalDefaultConnection = is_string($configuredDefault) ? $configuredDefault : 'sqlite';
        $this->database = 'oteryn_game_auth_'.bin2hex(random_bytes(8));
        $this->temporaryDirectory = sys_get_temp_dir().'/'.$this->database;

        if (! mkdir($this->temporaryDirectory, 0700) && ! is_dir($this->temporaryDirectory)) {
            self::fail('Unable to create the game-auth concurrency test directory.');
        }

        $this->root = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            'root',
            $rootPassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
        $this->root->exec('CREATE DATABASE `'.$this->database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        config()->set('database.connections.'.self::CONNECTION, [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $this->database,
            'username' => 'root',
            'password' => $rootPassword,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
        ]);
        config()->set('database.default', self::CONNECTION);
        DB::purge(self::CONNECTION);

        $exitCode = Artisan::call('migrate', [
            '--database' => self::CONNECTION,
            '--force' => true,
        ]);

        self::assertSame(0, $exitCode, Artisan::output());
    }

    protected function tearDown(): void
    {
        config()->set('database.default', $this->originalDefaultConnection);
        DB::purge(self::CONNECTION);

        if ($this->root instanceof PDO && is_string($this->database)) {
            $this->root->exec('DROP DATABASE IF EXISTS `'.$this->database.'`');
        }

        if (is_string($this->temporaryDirectory) && is_dir($this->temporaryDirectory)) {
            foreach (glob($this->temporaryDirectory.'/*') ?: [] as $path) {
                if (is_string($path) && is_file($path)) {
                    unlink($path);
                }
            }

            rmdir($this->temporaryDirectory);
        }

        parent::tearDown();
    }

    public function test_exactly_one_concurrent_redeem_succeeds_against_mariadb(): void
    {
        $identity = Identity::query()->create([
            'email' => 'concurrent-redeem@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);
        IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => 1001,
            'provisioning_name' => 'concurrent_'.$identity->id,
            'canary_creation_epoch' => 1,
            'status' => IdentityCanaryAccount::STATUS_READY,
            'ready_at' => now(),
        ]);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        DB::disconnect(self::CONNECTION);

        $firstPid = $this->forkChild(fn () => $this->runLockingRedeemer($issued->ticket));
        $secondPid = null;

        try {
            $this->waitForFile($this->path('first-locked'));
            $secondPid = $this->forkChild(fn () => $this->runWaitingRedeemer($issued->ticket));
            $secondConnectionId = $this->waitForConnectionId();
            $this->waitForInnoDbLockWait($secondConnectionId);
            touch($this->path('release-first'));

            $this->waitForChild($firstPid);
            $this->waitForChild($secondPid);

            $first = $this->readResult('first-result');
            $second = $this->readResult('second-result');
            $statuses = [$first['status'], $second['status']];

            self::assertSame('success', $first['status'], json_encode($first, JSON_THROW_ON_ERROR));
            self::assertSame('denied', $second['status'], json_encode($second, JSON_THROW_ON_ERROR));
            self::assertSame(1, count(array_filter(
                $statuses,
                static fn (string $status): bool => $status === 'success',
            )));

            DB::purge(self::CONNECTION);
            self::assertNotNull(GameLoginTicket::query()->sole()->used_at);
        } finally {
            touch($this->path('release-first'));
            $this->terminateChild($firstPid);

            if (is_int($secondPid)) {
                $this->terminateChild($secondPid);
            }
        }
    }

    private function runLockingRedeemer(string $ticket): void
    {
        $connection = DB::connection(self::CONNECTION);
        $connection->beginTransaction();

        try {
            $locked = GameLoginTicket::query()
                ->where('ticket_hash', hash('sha256', $ticket))
                ->lockForUpdate()
                ->first();

            if (! $locked instanceof GameLoginTicket) {
                throw new RuntimeException('The issued ticket row was not found.');
            }

            file_put_contents($this->path('first-locked'), 'locked', LOCK_EX);
            $this->waitForFile($this->path('release-first'));
            $redeemed = $this->app->make(RedeemGameLoginTicket::class)->execute(
                ticket: $ticket,
                audience: 'oteryn-game-gateway',
            );
            $connection->commit();
            $this->writeResult('first-result', [
                'status' => 'success',
                'canary_account_id' => $redeemed->canaryAccountId,
            ]);
        } catch (GameLoginTicketDenied) {
            $this->rollBackAll($connection);
            $this->writeResult('first-result', ['status' => 'denied']);
        } catch (Throwable $exception) {
            $this->rollBackAll($connection);
            $this->writeResult('first-result', [
                'status' => 'error',
                'exception' => $exception::class,
            ]);
        }
    }

    private function runWaitingRedeemer(string $ticket): void
    {
        try {
            $connection = DB::connection(self::CONNECTION);
            $row = $connection->selectOne('SELECT CONNECTION_ID() AS connection_id');
            $connectionId = is_object($row) ? ($row->connection_id ?? null) : null;

            if (! is_int($connectionId) && ! is_string($connectionId)) {
                throw new RuntimeException('Unable to resolve the waiting MariaDB connection ID.');
            }

            file_put_contents($this->path('second-connection-id'), (string) $connectionId, LOCK_EX);
            $redeemed = $this->app->make(RedeemGameLoginTicket::class)->execute(
                ticket: $ticket,
                audience: 'oteryn-game-gateway',
            );
            $this->writeResult('second-result', [
                'status' => 'success',
                'canary_account_id' => $redeemed->canaryAccountId,
            ]);
        } catch (GameLoginTicketDenied) {
            $this->writeResult('second-result', ['status' => 'denied']);
        } catch (Throwable $exception) {
            $this->writeResult('second-result', [
                'status' => 'error',
                'exception' => $exception::class,
            ]);
        }
    }

    private function forkChild(callable $operation): int
    {
        $pid = pcntl_fork();

        if ($pid === -1) {
            self::fail('Unable to fork a game-auth concurrency worker.');
        }

        if ($pid === 0) {
            DB::purge(self::CONNECTION);
            $this->root = null;
            $operation();
            exit(0);
        }

        return $pid;
    }

    private function waitForConnectionId(): int
    {
        $path = $this->path('second-connection-id');
        $this->waitForFile($path);
        $value = file_get_contents($path);

        if (! is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            self::fail('The waiting MariaDB connection ID is invalid.');
        }

        return (int) $value;
    }

    private function waitForInnoDbLockWait(int $connectionId): void
    {
        if (! $this->root instanceof PDO) {
            self::fail('MariaDB root connection is unavailable.');
        }

        $statement = $this->root->prepare(
            'SELECT COUNT(*) '
            .'FROM information_schema.INNODB_LOCK_WAITS AS waits '
            .'INNER JOIN information_schema.INNODB_TRX AS transactions '
            .'ON transactions.trx_id = waits.requesting_trx_id '
            .'WHERE transactions.trx_mysql_thread_id = :connection_id',
        );
        $deadline = microtime(true) + 10.0;

        do {
            $statement->execute(['connection_id' => $connectionId]);
            $count = $statement->fetchColumn();

            if ((is_int($count) || is_string($count)) && (int) $count > 0) {
                return;
            }

            usleep(10_000);
        } while (microtime(true) < $deadline);

        self::fail('The second redeem did not enter an observable InnoDB lock wait.');
    }

    private function waitForFile(string $path): void
    {
        $deadline = microtime(true) + 10.0;

        do {
            if (is_file($path)) {
                return;
            }

            usleep(10_000);
        } while (microtime(true) < $deadline);

        throw new RuntimeException('Timed out waiting for concurrency coordination file.');
    }

    private function waitForChild(int $pid): void
    {
        $deadline = microtime(true) + 10.0;

        do {
            $result = pcntl_waitpid($pid, $status, WNOHANG);

            if ($result === $pid) {
                self::assertTrue(pcntl_wifexited($status));
                self::assertSame(0, pcntl_wexitstatus($status));

                return;
            }

            usleep(10_000);
        } while (microtime(true) < $deadline);

        self::fail('A game-auth concurrency worker did not exit in time.');
    }

    private function terminateChild(int $pid): void
    {
        $result = pcntl_waitpid($pid, $status, WNOHANG);

        if ($result === 0) {
            posix_kill($pid, SIGKILL);
            pcntl_waitpid($pid, $status);
        }
    }

    private function rollBackAll(\Illuminate\Database\Connection $connection): void
    {
        while ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }
    }

    /**
     * @param  array<string, int|string>  $result
     */
    private function writeResult(string $name, array $result): void
    {
        file_put_contents(
            $this->path($name),
            json_encode($result, JSON_THROW_ON_ERROR),
            LOCK_EX,
        );
    }

    /**
     * @return array{status: string, canary_account_id?: int, exception?: string}
     */
    private function readResult(string $name): array
    {
        $path = $this->path($name);
        $this->waitForFile($path);
        $contents = file_get_contents($path);

        if (! is_string($contents)) {
            self::fail('Unable to read a concurrency result file.');
        }

        $result = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($result) || ! isset($result['status']) || ! is_string($result['status'])) {
            self::fail('A concurrency result file is invalid.');
        }

        $normalized = ['status' => $result['status']];

        if (isset($result['canary_account_id']) && is_int($result['canary_account_id'])) {
            $normalized['canary_account_id'] = $result['canary_account_id'];
        }

        if (isset($result['exception']) && is_string($result['exception'])) {
            $normalized['exception'] = $result['exception'];
        }

        return $normalized;
    }

    private function path(string $name): string
    {
        if (! is_string($this->temporaryDirectory)) {
            throw new RuntimeException('The concurrency test directory is unavailable.');
        }

        return $this->temporaryDirectory.'/'.$name;
    }
}
