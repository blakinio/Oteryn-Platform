<?php

namespace Tests\Unit\Identity\Sessions;

use App\Identity\Models\Identity;
use App\Identity\Sessions\IdentityWebSessionManager;
use App\Identity\Sessions\WebSessionState;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;

final class IdentityWebSessionManagerTest extends TestCase
{
    public function test_establish_regenerates_session_identifier_and_stores_generation(): void
    {
        $session = new Store('oteryn-test', new ArraySessionHandler(120));
        $session->start();
        $request = Request::create('/login', 'POST');
        $request->setLaravelSession($session);
        $identity = new Identity;
        $identity->web_session_generation = 7;
        $previousSessionId = $session->getId();

        (new IdentityWebSessionManager)->establish($request, $identity);

        self::assertNotSame($previousSessionId, $session->getId());
        self::assertSame(7, $session->get(WebSessionState::GENERATION_KEY));
    }
}
