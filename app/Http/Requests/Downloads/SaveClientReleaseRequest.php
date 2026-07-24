<?php

namespace App\Http\Requests\Downloads;

use App\Downloads\DownloadCatalog;
use App\Downloads\Models\ClientRelease;
use App\Downloads\Rules\ApprovedArtifactUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SaveClientReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $release = $this->route('clientRelease');
        $releaseId = $release instanceof ClientRelease ? $release->id : null;
        $channel = $this->input('channel');

        return [
            'version' => [
                'required',
                'string',
                'max:64',
                'regex:/^[0-9A-Za-z][0-9A-Za-z._+\-]{0,63}$/',
                Rule::unique('client_releases', 'version')
                    ->where(static fn ($query) => $query->where('channel', $channel))
                    ->ignore($releaseId),
            ],
            'channel' => ['required', 'string', Rule::in(DownloadCatalog::channels())],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'artifacts' => ['required', 'array', 'min:1', 'max:12'],
            'artifacts.*' => ['required', 'array'],
            'artifacts.*.platform' => ['required', 'string', Rule::in(DownloadCatalog::platforms())],
            'artifacts.*.architecture' => ['required', 'string', Rule::in(DownloadCatalog::architectures())],
            'artifacts.*.artifact_url' => ['required', 'string', 'max:2048', new ApprovedArtifactUrl],
            'artifacts.*.filename' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._()+ \-]{0,254}$/',
            ],
            'artifacts.*.size_bytes' => ['required', 'integer', 'min:1', 'max:21474836480'],
            'artifacts.*.sha256' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'artifacts.*.is_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $artifacts = $this->input('artifacts', []);

            if (! is_array($artifacts)) {
                return;
            }

            $variants = [];

            foreach ($artifacts as $index => $artifact) {
                if (! is_array($artifact)) {
                    continue;
                }

                $platform = $artifact['platform'] ?? null;
                $architecture = $artifact['architecture'] ?? null;

                if (! is_string($platform) || ! is_string($architecture)) {
                    continue;
                }

                $variant = $platform.'|'.$architecture;

                if (isset($variants[$variant])) {
                    $validator->errors()->add(
                        "artifacts.{$index}.architecture",
                        'Each operating-system and architecture variant may appear only once per release.',
                    );
                }

                $variants[$variant] = true;
            }
        }];
    }

    /**
     * @return list<array{platform: string, architecture: string, artifact_url: string, filename: string, size_bytes: int, sha256: string, is_enabled: bool}>
     */
    public function artifactInput(): array
    {
        $validated = $this->validated();
        $artifacts = $validated['artifacts'] ?? [];
        $result = [];

        if (! is_array($artifacts)) {
            return [];
        }

        foreach ($artifacts as $artifact) {
            if (! is_array($artifact)) {
                continue;
            }

            $result[] = [
                'platform' => (string) $artifact['platform'],
                'architecture' => (string) $artifact['architecture'],
                'artifact_url' => (string) $artifact['artifact_url'],
                'filename' => (string) $artifact['filename'],
                'size_bytes' => (int) $artifact['size_bytes'],
                'sha256' => (string) $artifact['sha256'],
                'is_enabled' => (bool) $artifact['is_enabled'],
            ];
        }

        return $result;
    }

    protected function prepareForValidation(): void
    {
        $rawArtifacts = $this->input('artifacts', []);

        if (! is_array($rawArtifacts)) {
            return;
        }

        $artifacts = [];

        foreach ($rawArtifacts as $artifact) {
            if (! is_array($artifact)) {
                $artifacts[] = $artifact;

                continue;
            }

            $normalized = [
                'platform' => is_string($artifact['platform'] ?? null) ? trim($artifact['platform']) : ($artifact['platform'] ?? null),
                'architecture' => is_string($artifact['architecture'] ?? null) ? trim($artifact['architecture']) : ($artifact['architecture'] ?? null),
                'artifact_url' => is_string($artifact['artifact_url'] ?? null) ? trim($artifact['artifact_url']) : ($artifact['artifact_url'] ?? null),
                'filename' => is_string($artifact['filename'] ?? null) ? trim($artifact['filename']) : ($artifact['filename'] ?? null),
                'size_bytes' => $artifact['size_bytes'] ?? null,
                'sha256' => is_string($artifact['sha256'] ?? null) ? strtolower(trim($artifact['sha256'])) : ($artifact['sha256'] ?? null),
                'is_enabled' => $artifact['is_enabled'] ?? false,
            ];

            $meaningful = array_filter(
                array_diff_key($normalized, ['is_enabled' => true]),
                static fn (mixed $value): bool => $value !== null && $value !== '',
            );

            if ($meaningful !== []) {
                $artifacts[] = $normalized;
            }
        }

        $this->merge([
            'version' => is_string($this->input('version')) ? trim((string) $this->input('version')) : $this->input('version'),
            'channel' => is_string($this->input('channel')) ? trim((string) $this->input('channel')) : $this->input('channel'),
            'release_notes' => is_string($this->input('release_notes')) ? trim((string) $this->input('release_notes')) : $this->input('release_notes'),
            'artifacts' => $artifacts,
        ]);
    }
}
