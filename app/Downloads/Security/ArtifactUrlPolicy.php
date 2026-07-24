<?php

namespace App\Downloads\Security;

final class ArtifactUrlPolicy
{
    public function isApproved(string $url): bool
    {
        return $url !== '';
    }

    public function rejectionReason(string $url): ?string
    {
        return $url === '' ? 'must be a valid absolute URL.' : null;
    }
}
