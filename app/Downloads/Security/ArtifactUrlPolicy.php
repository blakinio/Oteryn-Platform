<?php

namespace App\Downloads\Security;

use Illuminate\Container\Attributes\Config;

final readonly class ArtifactUrlPolicy
{
    /**
     * @param  list<string>  $allowedHosts
     */
    public function __construct(
        #[Config('downloads.allowed_artifact_hosts', [])]
        private array $allowedHosts = [],
    ) {}

    public function isApproved(string $url): bool
    {
        return $this->rejectionReason($url) === null;
    }

    public function rejectionReason(string $url): ?string
    {
        if ($url === '' || trim($url) !== $url) {
            return 'must be a normalized absolute URL.';
        }

        if (preg_match('/[\x00-\x20\x7F\\\\]/', $url) === 1 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return 'must be a valid absolute URL.';
        }

        $schemeSeparator = strpos($url, '://');

        if ($schemeSeparator === false || $schemeSeparator === 0) {
            return 'must be a valid absolute URL.';
        }

        $scheme = strtolower(substr($url, 0, $schemeSeparator));

        if ($scheme !== 'https') {
            return 'uses a scheme that is not approved.';
        }

        $remainder = substr($url, $schemeSeparator + 3);

        if ($remainder === '') {
            return 'must be a valid absolute URL.';
        }

        if (str_contains($remainder, '#')) {
            return 'must not contain a fragment.';
        }

        $authorityLength = strcspn($remainder, '/?');
        $authority = substr($remainder, 0, $authorityLength);
        $pathAndQuery = substr($remainder, $authorityLength);

        if ($authority === '') {
            return 'must be a valid absolute URL.';
        }

        if (str_contains($authority, '@')) {
            return 'must not contain URL user information.';
        }

        if (str_contains($authority, '[') || str_contains($authority, ']')) {
            return 'uses a host that is not approved.';
        }

        $host = $authority;
        $lastColon = strrpos($authority, ':');

        if ($lastColon !== false) {
            if (strpos($authority, ':') !== $lastColon) {
                return 'uses a host that is not approved.';
            }

            $port = substr($authority, $lastColon + 1);

            if ($port !== '443') {
                return 'must use the standard HTTPS port.';
            }

            $host = substr($authority, 0, $lastColon);
        }

        $normalizedHost = strtolower(rtrim($host, '.'));

        if ($normalizedHost === '' || ! in_array($normalizedHost, $this->allowedHosts(), true)) {
            return 'uses a host that is not approved.';
        }

        if ($pathAndQuery === '' || str_starts_with($pathAndQuery, '?')) {
            return 'must reference a concrete immutable artifact path.';
        }

        $queryOffset = strpos($pathAndQuery, '?');
        $path = $queryOffset === false
            ? $pathAndQuery
            : substr($pathAndQuery, 0, $queryOffset);

        if ($path === '' || $path === '/' || ! str_starts_with($path, '/')) {
            return 'must reference a concrete immutable artifact path.';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(): array
    {
        $allowedHosts = [];

        foreach ($this->allowedHosts as $configuredHost) {
            $host = strtolower(rtrim(trim($configuredHost), '.'));

            if ($host !== '' && ! in_array($host, $allowedHosts, true)) {
                $allowedHosts[] = $host;
            }
        }

        return $allowedHosts;
    }
}
