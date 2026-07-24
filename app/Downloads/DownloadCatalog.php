<?php

namespace App\Downloads;

final class DownloadCatalog
{
    public const CHANNEL_STABLE = 'stable';

    public const CHANNEL_BETA = 'beta';

    public const PLATFORM_WINDOWS = 'windows';

    public const PLATFORM_LINUX = 'linux';

    public const PLATFORM_MACOS = 'macos';

    public const ARCHITECTURE_X86_64 = 'x86_64';

    public const ARCHITECTURE_ARM64 = 'arm64';

    public const ARCHITECTURE_X86 = 'x86';

    /**
     * @return list<string>
     */
    public static function channels(): array
    {
        return [self::CHANNEL_STABLE, self::CHANNEL_BETA];
    }

    /**
     * @return list<string>
     */
    public static function platforms(): array
    {
        return [self::PLATFORM_WINDOWS, self::PLATFORM_LINUX, self::PLATFORM_MACOS];
    }

    /**
     * @return list<string>
     */
    public static function architectures(): array
    {
        return [self::ARCHITECTURE_X86_64, self::ARCHITECTURE_ARM64, self::ARCHITECTURE_X86];
    }

    public static function channelLabel(string $channel): string
    {
        return match ($channel) {
            self::CHANNEL_STABLE => 'Stable',
            self::CHANNEL_BETA => 'Beta',
            default => $channel,
        };
    }

    public static function platformLabel(string $platform): string
    {
        return match ($platform) {
            self::PLATFORM_WINDOWS => 'Windows',
            self::PLATFORM_LINUX => 'Linux',
            self::PLATFORM_MACOS => 'macOS',
            default => $platform,
        };
    }

    public static function architectureLabel(string $architecture): string
    {
        return match ($architecture) {
            self::ARCHITECTURE_X86_64 => 'x86-64',
            self::ARCHITECTURE_ARM64 => 'ARM64',
            self::ARCHITECTURE_X86 => 'x86',
            default => $architecture,
        };
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'TB') {
                return number_format($value, $value >= 100 ? 0 : 1).' '.$unit;
            }

            $value /= 1024;
        }

        return $bytes.' B';
    }
}
