<?php

namespace Tests\Feature\Security;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class TrustedProxySchemeTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setTrustedProxiesEnvironment('10.201.3.0/24');
        TrustProxies::flushState();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        TrustProxies::flushState();
        $this->setTrustedProxiesEnvironment(null);
    }

    public function test_configured_reverse_proxy_generates_external_https_login_action(): void
    {
        $response = $this->requestLoginPage(
            remoteAddress: '10.201.3.10',
            forwardedHost: 'platform.oteryn.test',
        );

        $response->assertOk()
            ->assertSee('action="https://platform.oteryn.test/login"', false);
    }

    public function test_untrusted_client_cannot_spoof_forwarded_scheme_or_host(): void
    {
        $response = $this->requestLoginPage(
            remoteAddress: '203.0.113.10',
            forwardedHost: 'attacker.example.test',
        );

        $response->assertOk()
            ->assertSee('action="http://platform-backend/login"', false)
            ->assertDontSee('attacker.example.test', false);
    }

    private function requestLoginPage(string $remoteAddress, string $forwardedHost): TestResponse
    {
        return $this
            ->withServerVariables([
                'REMOTE_ADDR' => $remoteAddress,
                'REMOTE_PORT' => '49152',
                'SERVER_ADDR' => '10.201.3.20',
                'SERVER_NAME' => 'platform-backend',
                'SERVER_PORT' => '8080',
                'REQUEST_SCHEME' => 'http',
                'HTTPS' => 'off',
            ])
            ->withHeaders([
                'Host' => 'platform-backend',
                'X-Forwarded-For' => '198.51.100.20',
                'X-Forwarded-Host' => $forwardedHost,
                'X-Forwarded-Port' => '443',
                'X-Forwarded-Proto' => 'https',
            ])
            ->get('/login');
    }

    private function setTrustedProxiesEnvironment(?string $value): void
    {
        if ($value === null) {
            putenv('TRUSTED_PROXIES');
            unset($_ENV['TRUSTED_PROXIES'], $_SERVER['TRUSTED_PROXIES']);

            return;
        }

        putenv('TRUSTED_PROXIES='.$value);
        $_ENV['TRUSTED_PROXIES'] = $value;
        $_SERVER['TRUSTED_PROXIES'] = $value;
    }
}
