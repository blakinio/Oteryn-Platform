<?php

namespace Tests\Feature\Characters;

use App\Accounts\Models\IdentityCanaryAccount;
use App\Characters\Contracts\CanaryCharacterCreationGateway;
use App\Characters\Data\CharacterCreationResult;
use App\Characters\Exceptions\CharacterNameConflict;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CharacterCreationTest extends TestCase
{
    use RefreshDatabase;

    private RecordingCharacterCreationGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new RecordingCharacterCreationGateway;
        $this->app->instance(CanaryCharacterCreationGateway::class, $this->gateway);
    }

    public function test_guest_cannot_open_or_submit_character_creation(): void
    {
        $this->get('/account/characters/create')->assertRedirect('/login');
        $this->post('/account/characters', [
            'name' => 'Alice Moon',
            'vocation' => 9,
            'sex' => 1,
        ])->assertRedirect('/login');
    }

    public function test_authenticated_form_is_available_and_contains_only_product_inputs(): void
    {
        $identity = $this->identityWithBinding(1001, IdentityCanaryAccount::STATUS_READY);
        $this->loginAsCurrentIdentity($identity);

        $response = $this->get('/account/characters/create');

        $response->assertOk();
        $response->assertSee('Create a character');
        $response->assertSee('name="_token"', false);
        $response->assertSee('name="name"', false);
        $response->assertSee('name="vocation"', false);
        $response->assertSee('name="sex"', false);
        $response->assertDontSee('name="account_id"', false);
    }

    public function test_ready_binding_drives_account_authorization_and_client_cannot_override_starter_state(): void
    {
        $identity = $this->identityWithBinding(1002, IdentityCanaryAccount::STATUS_READY);
        $this->loginAsCurrentIdentity($identity);
        $this->gateway->nextResult = new CharacterCreationResult(7001, 'Alice Moon', true);

        $response = $this->post('/account/characters', [
            'name' => '  aLiCe   moon  ',
            'vocation' => 9,
            'sex' => 1,
            'account_id' => 999999,
            'level' => 999,
            'town_id' => 999,
            'conditions' => 'client-controlled',
        ]);

        $response->assertRedirect(route('account.characters.create'));
        $response->assertSessionHas('status', 'Character Alice Moon created.');

        self::assertSame([
            [
                'account_id' => 1002,
                'name' => 'Alice Moon',
                'vocation' => 9,
                'sex' => 1,
            ],
        ], $this->gateway->calls);
    }

    public function test_pending_binding_fails_closed_before_gateway_invocation(): void
    {
        $identity = $this->identityWithBinding(null, IdentityCanaryAccount::STATUS_PENDING);
        $this->loginAsCurrentIdentity($identity);

        $response = $this->post('/account/characters', [
            'name' => 'Alice Moon',
            'vocation' => 1,
            'sex' => 0,
        ]);

        $response->assertSessionHasErrors('character');
        self::assertSame([], $this->gateway->calls);
    }

    public function test_invalid_name_fails_before_gateway_invocation(): void
    {
        $identity = $this->identityWithBinding(1003, IdentityCanaryAccount::STATUS_READY);
        $this->loginAsCurrentIdentity($identity);

        $response = $this->post('/account/characters', [
            'name' => 'OterynSupport',
            'vocation' => 1,
            'sex' => 0,
        ]);

        $response->assertSessionHasErrors('name');
        self::assertSame([], $this->gateway->calls);
    }

    public function test_invalid_vocation_is_rejected_by_request_validation(): void
    {
        $identity = $this->identityWithBinding(1004, IdentityCanaryAccount::STATUS_READY);
        $this->loginAsCurrentIdentity($identity);

        $this->post('/account/characters', [
            'name' => 'Alice Moon',
            'vocation' => 10,
            'sex' => 0,
        ])->assertSessionHasErrors('vocation');

        self::assertSame([], $this->gateway->calls);
    }

    public function test_name_conflict_is_returned_as_bounded_validation_error(): void
    {
        $identity = $this->identityWithBinding(1005, IdentityCanaryAccount::STATUS_READY);
        $this->loginAsCurrentIdentity($identity);
        $this->gateway->throwNameConflict = true;

        $this->post('/account/characters', [
            'name' => 'Alice Moon',
            'vocation' => 2,
            'sex' => 1,
        ])->assertSessionHasErrors('name');
    }

    public function test_existing_idempotent_result_is_reported_without_claiming_a_new_create(): void
    {
        $identity = $this->identityWithBinding(1006, IdentityCanaryAccount::STATUS_READY);
        $this->loginAsCurrentIdentity($identity);
        $this->gateway->nextResult = new CharacterCreationResult(7002, 'Alice Moon', false);

        $this->post('/account/characters', [
            'name' => 'Alice Moon',
            'vocation' => 4,
            'sex' => 1,
        ])->assertSessionHas('status', 'Character Alice Moon already exists on your account.');
    }

    private function identityWithBinding(?int $accountId, string $status): Identity
    {
        $identity = Identity::query()->create([
            'email' => uniqid('character-', true).'@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);

        IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => $accountId,
            'provisioning_name' => 'op'.substr(hash('sha256', (string) $identity->id), 0, 30),
            'canary_creation_epoch' => 1_800_000_000 + $identity->id,
            'status' => $status,
            'ready_at' => $status === IdentityCanaryAccount::STATUS_READY ? now() : null,
        ]);

        return $identity;
    }

    private function loginAsCurrentIdentity(Identity $identity): void
    {
        $this->post('/login', [
            'email' => $identity->email,
            'password' => 'Correct-Horse-9!Battery',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($identity, 'web');
    }
}

final class RecordingCharacterCreationGateway implements CanaryCharacterCreationGateway
{
    /** @var list<array{account_id: int, name: string, vocation: int, sex: int}> */
    public array $calls = [];

    public CharacterCreationResult $nextResult;

    public bool $throwNameConflict = false;

    public function __construct()
    {
        $this->nextResult = new CharacterCreationResult(7000, 'Alice Moon', true);
    }

    public function create(int $accountId, string $canonicalName, int $vocation, int $sex): CharacterCreationResult
    {
        $this->calls[] = [
            'account_id' => $accountId,
            'name' => $canonicalName,
            'vocation' => $vocation,
            'sex' => $sex,
        ];

        if ($this->throwNameConflict) {
            throw new CharacterNameConflict('conflict');
        }

        return $this->nextResult;
    }
}
