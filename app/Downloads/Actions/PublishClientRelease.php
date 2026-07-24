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
        $releaseId = $release->id;
        $channel = $release->channel;

        return DB::transaction(function () use ($actor, $releaseId, $channel, $makeCurrent): ClientRelease {
            $channelReleases = ClientRelease::query()
                ->where('channel', $channel)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $storedRelease = null;

            foreach ($channelReleases as $candidate) {
                if ($candidate->id === $releaseId) {
                    $storedRelease = $candidate;
                    break;
                }
            }

            if (! $storedRelease instanceof ClientRelease) {
                throw ValidationException::withMessages([
                    'release' => 'The release no longer exists in the selected channel.',
                ]);
            }

            if ($storedRelease->published_at !== null && ! $makeCurrent) {
                throw ValidationException::withMessages([
                    'release' => 'The release is already published.',
                ]);
            }

            if ($storedRelease->published_at !== null && $storedRelease->is_current && $makeCurrent) {
                throw ValidationException::withMessages([
                    'release' => 'The release is already the current build for this channel.',
                ]);
            }

            $artifacts = ClientReleaseArtifact::query()
                ->where('client_release_id', $storedRelease->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $enabledArtifactCount = 0;

            foreach ($artifacts as $artifact) {
                if (! $artifact->is_enabled) {
                    continue;
                }

                $reason = $this->artifactUrls->rejectionReason($artifact->artifact_url);

                if ($reason !== null) {
                    throw ValidationException::withMessages([
                        'artifacts' => "Artifact {$artifact->filename} {$reason}",
                    ]);
                }

                $enabledArtifactCount++;
            }

            if ($enabledArtifactCount === 0) {
                throw ValidationException::withMessages([
                    'artifacts' => 'At least one enabled artifact is required before publication.',
                ]);
            }

            $firstPublication = $storedRelease->published_at === null;

            if ($makeCurrent) {
                ClientRelease::query()
                    ->where('channel', $storedRelease->channel)
                    ->where('id', '!=', $storedRelease->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            if ($storedRelease->published_at === null) {
                $storedRelease->published_at = now();
            }

            $storedRelease->is_current = $makeCurrent || $storedRelease->is_current;
            $storedRelease->save();

            $this->audit->record(
                $actor->id,
                $firstPublication ? 'downloads.release_published' : 'downloads.release_current_set',
                'client_release',
                (string) $storedRelease->id,
                [
                    'version' => $storedRelease->version,
                    'channel' => $storedRelease->channel,
                    'current' => $storedRelease->is_current,
                    'enabled_artifact_count' => $enabledArtifactCount,
                ],
            );

            return $storedRelease->load('artifacts');
        }, 3);
    }
}
