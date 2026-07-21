<?php

namespace App\Accounts\ReadModels;

use App\Accounts\Actions\ProvisionCanaryAccount;
use App\Accounts\Models\IdentityCanaryAccount;
use App\Identity\Models\Identity;

final class AccountOverviewReadModel
{
    public const STATE_READY = 'ready';

    public const STATE_PENDING = 'pending';

    public const STATE_RECOVERABLE = 'recoverable';

    public const STATE_CONFLICT = 'conflict';

    public const STATE_UNAVAILABLE = 'unavailable';

    /**
     * @return array{
     *     state: string,
     *     label: string,
     *     message: string,
     *     retry_allowed: bool,
     *     character_creation_allowed: bool
     * }
     */
    public function forIdentity(Identity $identity): array
    {
        $binding = IdentityCanaryAccount::query()->whereKey($identity->id)->first();

        if ($binding === null) {
            return $this->unavailableState(
                'We cannot confirm your game account setup right now. Character creation remains unavailable. Contact support if this persists.',
            );
        }

        if ($binding->isReady()) {
            return [
                'state' => self::STATE_READY,
                'label' => 'Ready',
                'message' => 'Your game account setup is complete and character creation is available.',
                'retry_allowed' => false,
                'character_creation_allowed' => true,
            ];
        }

        if ($binding->isConflict()) {
            return [
                'state' => self::STATE_CONFLICT,
                'label' => 'Support required',
                'message' => 'We cannot complete your game account setup automatically. Contact support for assistance. No replacement account will be created automatically.',
                'retry_allowed' => false,
                'character_creation_allowed' => false,
            ];
        }

        if (
            $binding->status === IdentityCanaryAccount::STATUS_PENDING
            && $binding->last_failure_code === ProvisionCanaryAccount::FAILURE_DEPENDENCY_UNAVAILABLE
        ) {
            return [
                'state' => self::STATE_RECOVERABLE,
                'label' => 'Setup interrupted',
                'message' => 'Game account setup was interrupted by a temporary service problem. You can safely retry the existing setup request.',
                'retry_allowed' => true,
                'character_creation_allowed' => false,
            ];
        }

        if ($binding->status === IdentityCanaryAccount::STATUS_PENDING) {
            return [
                'state' => self::STATE_PENDING,
                'label' => 'Setup in progress',
                'message' => 'Your game account setup is still in progress. Character creation will become available after setup completes.',
                'retry_allowed' => false,
                'character_creation_allowed' => false,
            ];
        }

        return $this->unavailableState(
            'We cannot confirm a valid game account setup state. Character creation remains unavailable. Contact support for assistance.',
        );
    }

    /**
     * @return array{
     *     state: string,
     *     label: string,
     *     message: string,
     *     retry_allowed: bool,
     *     character_creation_allowed: bool
     * }
     */
    private function unavailableState(string $message): array
    {
        return [
            'state' => self::STATE_UNAVAILABLE,
            'label' => 'Support required',
            'message' => $message,
            'retry_allowed' => false,
            'character_creation_allowed' => false,
        ];
    }
}
