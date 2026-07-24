<?php

namespace App\Downloads\Actions;

use App\Audit\AdminAuditRecorder;
use App\Downloads\Models\ClientRelease;
use App\Downloads\Models\ClientReleaseArtifact;
use App\Downloads\Security\ArtifactUrlPolicy;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class PublishClientRelease
{
    public function __construct(
        private ArtifactUrlPolicy $artifactUrls,
        private AdminAuditRecorder $audit,
    ) {}

    public function execute(Identity $actor, ClientRelease $release, bool $makeCurrent): ClientRelease
    {
        return DB::transaction(function () use ($actor, $release, $makeCurrent): ClientRelease {
            $lockedReleases = ClientRelease::query()
                ->where('channel', $release->channel)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $release = $lockedReleases->firstWhere('id', $release->id);

            if (! $release instanceof ClientRelease) {
                throw ValidationException::withMessages([
                    'release' => 'The release no longer exists in the selected channel.',
                ]);
            }

            if ($release->published_at !== null && ! $makeCurrent) {
                throw ValidationException::withMessages([
                    'release' => 'The release is already published.',
                ]);
            }

            if ($release->published_at !== null && $release->is_current && $makeCurrent) {
                throw ValidationException::withMessages([
                    'release' => 'The release is already the current build for this channel.',
                ]);
            }

            $artifacts = ClientReleaseArtifact::query()
                ->where('client_release_id', $release->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $enabledArtifacts = $artifacts->where('is_enabled', true)->values();

            if ($enabledArtifacts->isEmpty()) {
                throw ValidationException::withMessages([
                    'artifacts' => 'At least one enabled artifact is required before publication.',
                ]);
            }

            foreach ($enabledArtifacts as $artifact) {
                $reason = $this->artifactUrls->rejectionReason($artifact->artifact_url);

                if ($reason !== null) {
                    throw ValidationException::withMessages([
                        'artifacts' => "Artifact {$artifact->filename} {$reason}",
                    ]);
                }
            }

            $firstPublication = $release->published_at === null;

            if ($makeCurrent) {
                ClientRelease::query()
                    ->where('channel', $release->channel)
                    ->where('id', '!=', $release->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            if ($release->published_at === null) {
                $release->published_at = now();
            }

            $release->is_current = $makeCurrent || $release->is_current;
            $release->save();

            $this->audit->record(
                $actor->id,
                $firstPublication ? 'downloads.release_published' : 'downloads.release_current_set',
                'client_release',
                (string) $release->id,
                [
                    'version' => $release->version,
                    'channel' => $release->channel,
                    'current' => $release->is_current,
                    'enabled_artifact_count' => $enabledArtifacts->count(),
                ],
            );

            return $release->load('artifacts');
        }, 3);
    }
}
