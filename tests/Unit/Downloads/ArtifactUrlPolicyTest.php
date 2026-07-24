<?php

namespace Tests\Unit\Downloads;

use App\Downloads\Security\ArtifactUrlPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ArtifactUrlPolicyTest extends TestCase
{
    #[DataProvider('rejectedUrls')]
    public function test_it_rejects_unsafe_or_unapproved_artifact_urls(string $url): void
    {
        config()->set('downloads.allowed_artifact_schemes', ['https']);
        config()->set('downloads.allowed_artifact_hosts', ['downloads.example.test']);

        self::assertFalse((new ArtifactUrlPolicy)->isApproved($url));
    }

    public function test_it_accepts_only_an_exact_configured_https_host_with_a_concrete_path(): void
    {
        config()->set('downloads.allowed_artifact_schemes', ['https']);
        config()->set('downloads.allowed_artifact_hosts', ['downloads.example.test']);

        self::assertTrue((new ArtifactUrlPolicy)->isApproved(
            'https://downloads.example.test/releases/1.2.3/oteryn-client.zip',
        ));
        self::assertFalse((new ArtifactUrlPolicy)->isApproved(
            'https://sub.downloads.example.test/releases/1.2.3/oteryn-client.zip',
        ));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rejectedUrls(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'data scheme' => ['data:application/octet-stream;base64,AA=='],
            'plain http' => ['http://downloads.example.test/releases/client.zip'],
            'unapproved host' => ['https://evil.example.test/releases/client.zip'],
            'approved-looking suffix' => ['https://downloads.example.test.evil.test/releases/client.zip'],
            'userinfo' => ['https://user@downloads.example.test/releases/client.zip'],
            'fragment' => ['https://downloads.example.test/releases/client.zip#download'],
            'nonstandard port' => ['https://downloads.example.test:8443/releases/client.zip'],
            'host root only' => ['https://downloads.example.test/'],
            'control character' => ["https://downloads.example.test/releases/client.zip\n"],
        ];
    }
}
