<?php

namespace App\Operations;

final class ProductionConfigurationVerifier
{
    /**
     * @return list<string>
     */
    public function inspect(): array
    {
        $violations = [];

        if (config('app.env') !== 'production') {
            $violations[] = 'APP_ENV must be production.';
        }

        if (config('app.debug') !== false) {
            $violations[] = 'APP_DEBUG must be disabled.';
        }

        $appKey = config('app.key');
        if (! is_string($appKey) || trim($appKey) === '') {
            $violations[] = 'APP_KEY must be configured.';
        }

        $appUrl = config('app.url');
        if (! is_string($appUrl) || trim($appUrl) === '') {
            $violations[] = 'APP_URL must be configured as an absolute HTTPS URL.';
        } else {
            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            $host = parse_url($appUrl, PHP_URL_HOST);

            if (! is_string($scheme) || strtolower($scheme) !== 'https') {
                $violations[] = 'APP_URL must use HTTPS.';
            }

            if (! is_string($host) || $host === '') {
                $violations[] = 'APP_URL must include a valid host.';
            } elseif ($this->isLocalHost($host)) {
                $violations[] = 'APP_URL must not use a localhost or loopback host.';
            }
        }

        if (config('session.secure') !== true) {
            $violations[] = 'Secure session cookies must be enabled.';
        }

        if (config('session.http_only') !== true) {
            $violations[] = 'HttpOnly session cookies must be enabled.';
        }

        if (! $this->hasDeliveryCapableMailer()) {
            $violations[] = 'The default mailer must use a delivery-capable transport.';
        }

        $fromAddress = config('mail.from.address');
        if (! is_string($fromAddress) || ! filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $violations[] = 'MAIL_FROM_ADDRESS must be a valid email address.';
        } elseif ($this->usesReservedTestDomain($fromAddress)) {
            $violations[] = 'MAIL_FROM_ADDRESS must not use a reserved test domain.';
        }

        return $violations;
    }

    private function hasDeliveryCapableMailer(): bool
    {
        $defaultMailer = config('mail.default');

        if (! is_string($defaultMailer) || $defaultMailer === '') {
            return false;
        }

        $transport = config("mail.mailers.{$defaultMailer}.transport");

        return is_string($transport)
            && $transport !== ''
            && ! in_array(strtolower($transport), ['array', 'log'], true);
    }

    private function isLocalHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));

        return $host === 'localhost'
            || str_ends_with($host, '.localhost')
            || str_starts_with($host, '127.')
            || $host === '::1';
    }

    private function usesReservedTestDomain(string $email): bool
    {
        $separator = strrpos($email, '@');

        if ($separator === false) {
            return true;
        }

        $domain = strtolower(substr($email, $separator + 1));

        foreach (['.test', '.example', '.invalid', '.localhost'] as $suffix) {
            if ($domain === ltrim($suffix, '.') || str_ends_with($domain, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
