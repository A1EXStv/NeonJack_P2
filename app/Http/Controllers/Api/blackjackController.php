<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partida;
use App\Models\Sala;
use App\Models\PartidaUser;
use App\Services\BlackjackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlackjackController extends Controller
{
    public function __construct(
        private BlackjackService $blackjack
    ) {}

    // ─── Iniciar partida ──────────────────────────────────────────
    // Solo el dueño de la sala puede iniciarla.

    public function iniciar(Sala $sala): JsonResponse
    {
        if ($sala->owner_id !== Auth::id()) {
            return response()->json(['message' => 'Solo el dueño puede iniciar la partida.'], 403);
        }

        if ($sala->status !== 'waiting') {
            return response()->json(['message' => 'Ya hay una partida en curso.'], 422);
        }

        if ($sala->activePlayers()->count() < 1) {
            return response()->json(['message' => 'Necesitas al menos un jugador.'], 422);
        }

        $partida = $this->blackjack->iniciarPartida($sala);

        return response()->json($partida->load('partida_usuarios'), 201);
    }

    // ─── Apostar ──────────────────────────────────────────────────

    public function apostar(Request $request, Partida $partida): JsonResponse
    {
        $request->validate([
            'apuesta' => 'required|integer|min:1',
        ]);

        try {
            $pu = $this->blackjack->apostar($partida, Auth::user(), $request->apuesta);
            return response()->json($pu);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ─── Pedir carta (hit) ────────────────────────────────────────

    public function hit(Partida $partida): JsonResponse
    {
        try {
            $pu = $this->blackjack->hit($partida, Auth::user());
            return response()->json($pu);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ─── Plantarse (stand) ────────────────────────────────────────

    public function stand(Partida $partida): JsonResponse
    {
        try {
            $pu = $this->blackjack->stand($partida, Auth::user());
            return response()->json($pu);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ─── Doblar (double down) ─────────────────────────────────────

    public function doblar(Partida $partida): JsonResponse
    {
        try {
            $pu = $this->blackjack->doblar($partida, Auth::user());
            return response()->json($pu);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ─── Dividir (split) ─────────────────────────────────────────

    public function dividir(Partida $partida): JsonResponse
    {
        try {
            $manos = $this->blackjack->dividir($partida, Auth::user());
            return response()->json($manos);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ─── Estado actual de la partida ──────────────────────────────

    public function estado(Partida $partida): JsonResponse
    {
        return response()->json(
            $partida->load('partida_usuarios.usuario')
        );
    }
}