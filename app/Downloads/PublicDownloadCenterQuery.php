<?php

namespace App\Downloads;

use App\Downloads\Models\ClientRelease;
use App\Downloads\Models\ClientReleaseArtifact;
use App\Downloads\Security\ArtifactUrlPolicy;
use App\Downloads\ViewModels\DownloadCenterViewModel;
use Throwable;

final readonly class PublicDownloadCenterQuery
{
    public function __construct(private ArtifactUrlPolicy $artifactUrls) {}

    public function get(?string $platform = null): DownloadCenterViewModel
    {
        try {
            $releases = ClientRelease::query()
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where('is_current', true)
                ->with(['artifacts' => static function ($query): void {
                    $query
                        ->where('is_enabled', true)
                        ->orderBy('platform')
                        ->orderBy('architecture')
                        ->orderBy('id');
                }])
                ->orderByRaw('CASE WHEN channel = ? THEN 0 ELSE 1 END', [DownloadCatalog::CHANNEL_STABLE])
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get();
        } catch (Throwable) {
            return new DownloadCenterViewModel(DownloadCenterState::UNAVAILABLE, [], $platform);
        }

        if ($releases->isEmpty()) {
            return new DownloadCenterViewModel(DownloadCenterState::EMPTY, [], $platform);
        }

        $publicReleases = [];
        $rejectedArtifactSeen = false;

        foreach ($releases as $release) {
            $approved = $release->artifacts->filter(function (ClientReleaseArtifact $artifact) use (&$rejectedArtifactSeen): bool {
                if ($this->artifactUrls->isApproved($artifact->artifact_url)) {
                    return true;
                }

                $rejectedArtifactSeen = true;

                return false;
            });

            if ($platform !== null) {
                $approved = $approved->where('platform', $platform);
            }

            if ($approved->isEmpty()) {
                continue;
            }

            $release->setRelation('artifacts', $approved->values());
            $publicReleases[] = $release;
        }

        if ($publicReleases === []) {
            return new DownloadCenterViewModel(
                $rejectedArtifactSeen ? DownloadCenterState::UNAVAILABLE : DownloadCenterState::EMPTY,
                [],
                $platform,
            );
        }

        /** @var list<ClientRelease> $publicReleases */
        return new DownloadCenterViewModel(DownloadCenterState::AVAILABLE, $publicReleases, $platform);
    }
}
