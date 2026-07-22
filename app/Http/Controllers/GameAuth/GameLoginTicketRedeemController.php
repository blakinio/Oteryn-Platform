<?php

namespace App\Http\Controllers\GameAuth;

use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\GameAuth\Tickets\RedeemGameLoginTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

final class GameLoginTicketRedeemController
{
    public function __invoke(Request $request, RedeemGameLoginTicket $redeemer): JsonResponse
    {
        $audience = config('game-auth.ticket.audience');

        if (! is_string($audience) || $audience === '') {
            throw new LogicException('Game ticket audience is not configured.');
        }

        $request->validate([
            'protocol_version' => ['required', 'integer', 'in:1'],
            'ticket' => ['required', 'string', 'max:1024'],
            'audience' => ['required', 'string', 'in:'.$audience],
            'identity_id' => ['prohibited'],
            'account_id' => ['prohibited'],
            'canary_account_id' => ['prohibited'],
        ]);

        $ticket = $request->input('ticket');

        if (! is_string($ticket)) {
            return response()->json(['error' => 'invalid_request'], 422);
        }

        try {
            $redeemed = $redeemer->execute($ticket, $audience);
        } catch (GameLoginTicketDenied) {
            return response()->json(['error' => 'invalid_ticket'], 401);
        }

        return response()->json([
            'protocol_version' => 1,
            'authorization' => [
                'canary_account_id' => $redeemed->canaryAccountId,
                'security_generation' => $redeemed->securityGeneration,
                'redeemed_at' => $redeemed->redeemedAt->toISOString(),
            ],
        ]);
    }
}
