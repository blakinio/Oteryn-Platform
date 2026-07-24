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

        if ($this->allowedSchemes() === []) {
            return 'uses a scheme that is not approved.';
        }

        if ($this->allowedHosts() === []) {
            return 'uses a host that is not approved.';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function allowedSchemes(): array
    {
        return $this->normalizeConfigList(
            config('downloads.allowed_artifact_schemes', ['https']),
            false,
        );
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(): array
    {
        return $this->normalizeConfigList(
            config('downloads.allowed_artifact_hosts', []),
            true,
        );
    }

    /**
     * @return list<string>
     */
    private function normalizeConfigList(mixed $configured, bool $trimTrailingDot): array
    {
        if (! is_array($configured)) {
            return [];
        }

        /** @var list<string> $normalizedValues */
        $normalizedValues = [];

        foreach ($configured as $value) {
            if (! is_string($value)) {
                continue;
            }

            $normalized = strtolower(trim($value));

            if ($trimTrailingDot) {
                $normalized = rtrim($normalized, '.');
            }

            if ($normalized !== '' && ! in_array($normalized, $normalizedValues, true)) {
                $normalizedValues[] = $normalized;
            }
        }

        return $normalizedValues;
    }
}
