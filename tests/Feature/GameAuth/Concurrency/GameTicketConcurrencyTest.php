<?php

namespace Tests\Feature\GameAuth\Concurrency;

use App\Accounts\Models\IdentityCanaryAccount;
use App\GameAuth\OAuth\IssueGameLoginTicketFromOAuth;
use App\GameAuth\OAuth\NativeOAuthClientManager;
use App\GameAuth\OAuth\OAuthBootstrapDenied;
use App\GameAuth\Tickets\GameLoginTicket;
use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\GameAuth\Tickets\RedeemGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Tests\TestCase;
use Throwable;

final class GameTicketConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('GAME_AUTH_CONCURRENCY_TEST') !== '1' || ! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Requires the dedicated MariaDB concurrency workflow with pcntl.');
        }
    }

    public function test_exactly_one_concurrent_ticket_redeem_succeeds_across_independent_processes(): void
    {
        $identity = $this->createIdentityWithReadyBinding(1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $results = $this->race(function () use ($issued): string {
            try {
                $this->app->make(RedeemGameLoginTicket::class)
                    ->execute($issued->ticket, 'oteryn-game-gateway');

                return 'success';
            } catch (GameLoginTicketDenied) {
                return 'denied';
            }
        });

        sort($results);
        self::assertSame(['denied', 'success'], $results);
        self::assertNotNull(GameLoginTicket::query()->firstOrFail()->used_at);
    }

    public function test_exactly_one_concurrent_oauth_exchange_mints_a_ticket_and_revokes_token_family(): void
    {
        $identity = $this->createIdentityWithReadyBinding(1001);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        $accessTokenId = Str::random(80);

        Token::query()->create([
            'id' => $accessTokenId,
            'user_id' => $identity->id,
            'client_id' => $client->getKey(),
            'name' => null,
            'scopes' => ['game:ticket'],
            'revoked' => false,
            'expires_at' => now()->addMinutes(5),
        ]);
        RefreshToken::query()->create([
            'id' => Str::random(80),
            'access_token_id' => $accessTokenId,
            'revoked' => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        $results = $this->race(function () use ($identity, $accessTokenId): string {
            try {
                $freshIdentity = Identity::query()->findOrFail($identity->id);
                $this->app->make(IssueGameLoginTicketFromOAuth::class)
                    ->execute($freshIdentity, $accessTokenId);

                return 'success';
            } catch (OAuthBootstrapDenied|GameLoginTicketDenied) {
                return 'denied';
            }
        });

        sort($results);
        self::assertSame(['denied', 'success'], $results);
        self::assertSame(1, GameLoginTicket::query()->count());
        self::assertTrue(Token::query()->findOrFail($accessTokenId)->revoked);
        self::assertTrue((bool) RefreshToken::query()
            ->where('access_token_id', $accessTokenId)
            ->value('revoked'));
    }

    /**
     * @param  callable(): string  $operation
     * @return list<string>
     */
    private function race(callable $operation): array
    {
        $directory = sys_get_temp_dir().'/oteryn-game-auth-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0700));
        $children = [];

        for ($index = 0; $index < 2; $index++) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                self::fail('Unable to fork concurrency test process.');
            }

            if ($pid === 0) {
                DB::disconnect();
                DB::purge();
                file_put_contents($directory.'/ready-'.$index, '1');

                while (! file_exists($directory.'/start')) {
                    usleep(1000);
                }

                try {
                    $result = $operation();
                } catch (Throwable $exception) {
                    $result = 'error:'.$exception::class;
                }

                file_put_contents($directory.'/result-'.$index, $result);
                exit(0);
            }

            $children[] = $pid;
        }

        $deadline = microtime(true) + 10;

        while ((! file_exists($directory.'/ready-0') || ! file_exists($directory.'/ready-1'))
            && microtime(true) < $deadline
        ) {
            usleep(1000);
        }

        self::assertFileExists($directory.'/ready-0');
        self::assertFileExists($directory.'/ready-1');
        file_put_contents($directory.'/start', '1');

        foreach ($children as $pid) {
            $status = 0;
            pcntl_waitpid($pid, $status);
            self::assertTrue(pcntl_wifexited($status));
            self::assertSame(0, pcntl_wexitstatus($status));
        }

        $results = [];

        for ($index = 0; $index < 2; $index++) {
            $path = $directory.'/result-'.$index;
            self::assertFileExists($path);
            $result = file_get_contents($path);
            self::assertIsString($result);
            $results[] = $result;
        }

        foreach (glob($directory.'/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($directory);
        DB::purge();
        DB::reconnect();

        return $results;
    }

    private function createIdentityWithReadyBinding(int $canaryAccountId): Identity
    {
        $identity = Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);

        IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => $canaryAccountId,
            'provisioning_name' => 'ready_'.$identity->id,
            'canary_creation_epoch' => 1,
            'status' => IdentityCanaryAccount::STATUS_READY,
            'ready_at' => now(),
        ]);

        return $identity;
    }
}
