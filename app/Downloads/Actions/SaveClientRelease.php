<?php

namespace App\Downloads\Actions;

use App\Audit\AdminAuditRecorder;
use App\Downloads\Models\ClientRelease;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class SaveClientRelease
{
    public function __construct(private AdminAuditRecorder $audit) {}

    /**
     * @param  list<array{platform: string, architecture: string, artifact_url: string, filename: string, size_bytes: int, sha256: string, is_enabled: bool}>  $artifacts
     */
    public function execute(
        Identity $actor,
        ?ClientRelease $release,
        string $version,
        string $channel,
        ?string $releaseNotes,
        array $artifacts,
    ): ClientRelease {
        return DB::transaction(function () use (
            $actor,
            $release,
            $version,
            $channel,
            $releaseNotes,
            $artifacts,
        ): ClientRelease {
            $created = $release === null;

            if ($release === null) {
                $release = new ClientRelease;
            } else {
                $release = ClientRelease::query()->lockForUpdate()->findOrFail($release->id);

                if ($release->published_at !== null) {
                    throw ValidationException::withMessages([
                        'release' => 'Published releases are immutable. Create a new release for changed artifact metadata.',
                    ]);
                }
            }

            $release->fill([
                'version' => $version,
                'channel' => $channel,
                'release_notes' => $releaseNotes,
                'published_at' => null,
                'is_current' => false,
            ]);
            $release->save();

            $release->artifacts()->delete();
            $release->artifacts()->createMany($artifacts);

            $this->audit->record(
                $actor->id,
                $created ? 'downloads.release_created' : 'downloads.release_updated',
                'client_release',
                (string) $release->id,
                [
                    'version' => $release->version,
                    'channel' => $release->channel,
                    'artifact_count' => count($artifacts),
                    'enabled_artifact_count' => count(array_filter(
                        $artifacts,
                        static fn (array $artifact): bool => $artifact['is_enabled'],
                    )),
                ],
            );

            return $release->load('artifacts');
        }, 3);
    }
}
