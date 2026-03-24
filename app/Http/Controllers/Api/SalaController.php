<?php

namespace App\Http\Controllers\Api;

use App\Events\PlayerJoinedRoom;
use App\Events\PlayerLeftRoom;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaRequest;
use App\Http\Requests\UpdateSalaRequest;
use App\Models\Sala;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaController extends Controller
{
    /**
     * Muestra la lista de salas con sus manos y usuarios.
     */
    public function index(): JsonResponse
    {
        $salas = Sala::with(['manos.usuarios'])->latest()->get();
        return response()->json($salas);
    }

    /**
     * Almacena una sala recién creada.
     * El creador entra automáticamente en el asiento 1.
     */
    public function store(StoreSalaRequest $request): JsonResponse
    {
        $sala = DB::transaction(function () use ($request) {
            $sala = Sala::create([
                ...$request->validated(),
                'owner_id' => Auth::id(),
            ]);

            $sala->players()->attach(Auth::id(), [
                'status' => 'sitting',
                'chips'  => 1000,
                'seat'   => 1,
            ]);

            return $sala;
        });

        broadcast(new PlayerJoinedRoom($sala, Auth::user(), 1))->toOthers();

        return response()->json($sala->load('manos.usuarios'), 201);
    }

    /**
     * Muestra una sala específica.
     */
    public function show(Sala $sala): JsonResponse
    {
        return response()->json($sala->load('manos.usuarios'));
    }

    /**
     * Actualiza una sala.
     */
    public function update(UpdateSalaRequest $request, Sala $sala): JsonResponse
    {
        $sala->update($request->validated());
        return response()->json($sala);
    }

    /**
     * Elimina una sala.
     */
    public function destroy(Sala $sala): JsonResponse
    {
        $sala->delete();
        return response()->json($sala);
    }

    /**
     * Un jugador se une a la sala.
     */
    public function join(Sala $sala): JsonResponse
    {
        $user = Auth::user();

        if ($sala->hasPlayer($user->id)) {
            return response()->json(['message' => 'Ya estás en esta sala.'], 422);
        }

        if ($sala->isFull()) {
            return response()->json(['message' => 'La sala está llena.'], 422);
        }

        if ($sala->status !== 'waiting') {
            return response()->json(['message' => 'La partida ya ha comenzado.'], 422);
        }

        $seat = $sala->availableSeat();

        DB::transaction(function () use ($sala, $user, $seat) {
            $sala->players()->attach($user->id, [
                'status' => 'sitting',
                'chips'  => 1000,
                'seat'   => $seat,
            ]);
        });

        broadcast(new PlayerJoinedRoom($sala, $user, $seat))->toOthers();

        return response()->json([
            'message' => 'Te has unido a la sala.',
            'seat'    => $seat,
            'sala'    => $sala->fresh('manos.usuarios'),
        ]);
    }

    /**
     * Un jugador sale de la sala.
     * Si era el dueño, transfiere ownership al siguiente jugador.
     * Si era el último, elimina la sala.
     */
    public function leave(Sala $sala): JsonResponse
    {
        $user = Auth::user();

        if (! $sala->hasPlayer($user->id)) {
            return response()->json(['message' => 'No estás en esta sala.'], 422);
        }

        DB::transaction(function () use ($sala, $user) {
            $sala->players()->detach($user->id);

            if ($sala->owner_id === $user->id) {
                $next = $sala->activePlayers()->first();
                if ($next) {
                    $sala->update(['owner_id' => $next->id]);
                } else {
                    $sala->delete();
                }
            }
        });

        if (! $sala->exists) {
            return response()->json(['message' => 'Sala eliminada (estaba vacía).']);
        }

        broadcast(new PlayerLeftRoom($sala, $user))->toOthers();

        return response()->json(['message' => 'Has salido de la sala.']);
    }
}