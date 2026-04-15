<?php

namespace App\Http\Controllers\Api;

use App\Events\PlayerJoinedRoom;
use App\Events\PlayerLeftRoom;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaRequest;
use App\Http\Requests\UpdateSalaRequest;
use App\Models\PartidaUser;
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
        $salas = Sala::with(['players'])
            ->whereIn('status', ['waiting', 'playing'])
            ->latest()
            ->get()
            ->map(fn ($sala) => array_merge($sala->toArray(), [
                'isFull' => $sala->isFull(),
            ]));
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
     * Muestra una sala específica con jugadores y la partida activa (si existe).
     */
    public function show(Sala $sala): JsonResponse
    {
        return response()->json($sala->load([
            'players',
            'partidas' => fn ($q) => $q->where('estado', 'en_curso')->latest()->limit(1),
        ]));
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

        // Durante la fase de apuesta se puede entrar; en cualquier otro momento no.
        $partidaEnApuesta = null;
        if ($sala->status === 'playing') {
            $partida = $sala->partidas()
                ->where('estado', 'en_curso')
                ->latest()
                ->first();

            if (! $partida || ! $partida->partida_usuarios()->where('estado', 'apostando')->exists()) {
                return response()->json(['message' => 'La partida ya está en curso y no está en fase de apuesta.'], 422);
            }
            $partidaEnApuesta = $partida;
        }

        $seat = $sala->availableSeat();

        DB::transaction(function () use ($sala, $user, $seat, $partidaEnApuesta) {
            $sala->players()->attach($user->id, [
                'status' => 'sitting',
                'chips'  => 1000,
                'seat'   => $seat,
            ]);

            // Si hay una partida en fase de apuesta, incluir al nuevo jugador
            if ($partidaEnApuesta) {
                PartidaUser::create([
                    'partida_id'    => $partidaEnApuesta->id,
                    'user_id'       => $user->id,
                    'apuesta_total' => 0,
                    'mano_usuario'  => [],
                    'estado'        => 'apostando',
                ]);
            }
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
        $user    = Auth::user();
        $isOwner = $sala->owner_id === $user->id;

        DB::transaction(function () use ($sala, $user, $isOwner) {
            $sala->players()->detach($user->id);

            if ($isOwner) {
                $next = $sala->activePlayers()->first();
                if ($next) {
                    $sala->update(['owner_id' => $next->id]);
                } else {
                    // Sin jugadores → cerrar sala y desconectar a todos
                    $sala->players()->detach();
                    $sala->update(['status' => 'finished']);
                }
            }
        });

        $sala->refresh();

        if ($sala->status === 'finished') {
            return response()->json(['closed' => true, 'message' => 'Sala cerrada.']);
        }

        broadcast(new PlayerLeftRoom($sala, $user))->toOthers();

        return response()->json(['closed' => false, 'message' => 'Has salido de la sala.']);
    }
}