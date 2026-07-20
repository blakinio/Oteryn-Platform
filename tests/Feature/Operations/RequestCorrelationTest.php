<?php

namespace Tests\Feature\Operations;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Tests\TestCase;

final class RequestCorrelationTest extends TestCase
{
    public function test_server_generated_request_id_is_returned_and_does_not_trust_inbound_header(): void
    {
        $response = $this
            ->withHeader('X-Request-ID', 'attacker-controlled-id')
            ->get('/');

        $requestId = $response->headers->get('X-Request-ID');

        self::assertIsString($requestId);
        self::assertTrue(Str::isUuid($requestId));
        self::assertNotSame('attacker-controlled-id', $requestId);
    }

    public function test_each_request_receives_a_distinct_request_id(): void
    {
        $first = $this->get('/')->headers->get('X-Request-ID');
        $second = $this->get('/')->headers->get('X-Request-ID');

        self::assertIsString($first);
        self::assertIsString($second);
        self::assertNotSame($first, $second);
    }

    public function test_request_completion_log_contains_only_bounded_safe_context(): void
    {
        $log = Log::spy();

        $response = $this->get('/?token=do-not-log&email=private@example.com');
        $requestId = $response->headers->get('X-Request-ID');

        self::assertIsString($requestId);

        $log->shouldHaveReceived('info')
            ->once()
            ->withArgs(function (mixed $message, mixed $context) use ($requestId): bool {
                if ($message !== 'http.request.completed' || ! is_array($context)) {
                    return false;
                }

                self::assertSame(
                    ['request_id', 'method', 'route', 'status', 'duration_ms'],
                    array_keys($context),
                );
                self::assertSame($requestId, $context['request_id'] ?? null);
                self::assertSame('GET', $context['method'] ?? null);
                self::assertSame('home', $context['route'] ?? null);
                self::assertSame(200, $context['status'] ?? null);

                $duration = $context['duration_ms'] ?? null;
                self::assertIsFloat($duration);
                self::assertGreaterThanOrEqual(0, $duration);

                return true;
            });
    }

    public function test_health_route_is_also_correlated(): void
    {
        $requestId = $this->get('/health')->headers->get('X-Request-ID');

        self::assertIsString($requestId);
        self::assertTrue(Str::isUuid($requestId));
    }

    public function test_optional_json_stderr_logging_channel_is_available(): void
    {
        self::assertSame('monolog', config('logging.channels.stderr_json.driver'));
        self::assertSame(StreamHandler::class, config('logging.channels.stderr_json.handler'));
        self::assertSame('php://stderr', config('logging.channels.stderr_json.handler_with.stream'));
        self::assertSame(JsonFormatter::class, config('logging.channels.stderr_json.formatter'));
    }
}
