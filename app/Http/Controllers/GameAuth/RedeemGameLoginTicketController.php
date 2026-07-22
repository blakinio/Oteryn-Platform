<?php

namespace App\Http\Controllers\GameAuth;

use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\GameAuth\Tickets\RedeemGameLoginTicket;
use App\Http\Requests\GameAuth\RedeemGameLoginTicketRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RedeemGameLoginTicketController
{
    public function __invoke(
        RedeemGameLoginTicketRequest $request,
        RedeemGameLoginTicket $redeemer,
    ): JsonResponse {
        $validated = $request->validated();

        try {
            $redeemed = $redeemer->execute(
                ticket: $validated['ticket'],
                audience: $validated['audience'],
            );
        } catch (GameLoginTicketDenied) {
            return $this->error('invalid_ticket', 'The game login ticket is invalid or expired.', Response::HTTP_UNAUTHORIZED);
        } catch (QueryException) {
            return $this->error('temporarily_unavailable', 'Game authentication is temporarily unavailable.', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json([
            'protocol_version' => config('game-auth.protocol_version'),
            'authorization' => [
                'canary_account_id' => $redeemed->canaryAccountId,
                'security_generation' => $redeemed->securityGeneration,
                'redeemed_at' => $redeemed->redeemedAt->toIso8601String(),
            ],
        ], Response::HTTP_OK, $this->noStoreHeaders());
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status, $this->noStoreHeaders());
    }

    /**
     * @return array<string, string>
     */
    private function noStoreHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ];
    }
}
