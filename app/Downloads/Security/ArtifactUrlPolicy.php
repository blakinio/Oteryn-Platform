<?php

namespace App\Downloads\Security;

final class ArtifactUrlPolicy
{
    public function isApproved(string $url): bool
    {
        return $this->rejectionReason($url) === null;
    }

    public function rejectionReason(string $url): ?string
    {
        if ($url === '' || trim($url) !== $url) {
            return 'must be a normalized absolute URL.';
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return 'must be a valid absolute URL.';
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return 'must be a valid absolute URL.';
        }

        $scheme = isset($parts['scheme']) && is_string($parts['scheme'])
            ? strtolower($parts['scheme'])
            : '';
        $host = isset($parts['host']) && is_string($parts['host'])
            ? strtolower(rtrim($parts['host'], '.'))
            : '';
        $path = isset($parts['path']) && is_string($parts['path'])
            ? $parts['path']
            : '';

        if (! in_array($scheme, $this->allowedSchemes(), true)) {
            return 'uses a scheme that is not approved.';
        }

        if ($host === '' || ! in_array($host, $this->allowedHosts(), true)) {
            return 'uses a host that is not approved.';
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'must not contain URL user information.';
        }

        if (isset($parts['fragment'])) {
            return 'must not contain a fragment.';
        }

        if (isset($parts['port']) && $parts['port'] !== 443) {
            return 'must use the standard HTTPS port.';
        }

        if ($path === '' || $path === '/') {
            return 'must reference a concrete immutable artifact path.';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function allowedSchemes(): array
    {
        $schemes = config('downloads.allowed_artifact_schemes', ['https']);

        if (! is_array($schemes)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $scheme): string => is_string($scheme) ? strtolower(trim($scheme)) : '',
            $schemes,
        ))));
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(): array
    {
        $hosts = config('downloads.allowed_artifact_hosts', []);

        if (! is_array($hosts)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $host): string => is_string($host) ? strtolower(rtrim(trim($host), '.')) : '',
            $hosts,
        ))));
    }
}
