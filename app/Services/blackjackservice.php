<?php

namespace App\Services;

use App\Events\CardDealt;
use App\Events\GameStarted;
use App\Events\RoundEnded;
use App\Events\TurnChanged;
use App\Models\Ajustes as Ajuste;
use App\Models\Mano;
use App\Models\Partida;
use App\Models\PartidaUser as PartidaUsuario;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BlackjackService
{
    // ─── Baraja ───────────────────────────────────────────────────

    private function generarBaraja(): array
    {
        $palos  = ['hearts', 'diamonds', 'clubs', 'spades'];
        $valores = ['2','3','4','5','6','7','8','9','10','J','Q','K','A'];
        $baraja = [];

        foreach ($palos as $palo) {
            foreach ($valores as $valor) {
                $baraja[] = ['palo' => $palo, 'valor' => $valor];
            }
        }

        shuffle($baraja);
        return $baraja;
    }

    // ─── Iniciar partida ──────────────────────────────────────────

    public function iniciarPartida(Sala $sala): Partida
    {
        $ajuste  = Ajuste::first();
        $jugadores = $sala->activePlayers()->get();

        return DB::transaction(function () use ($sala, $jugadores, $ajuste) {
            $baraja = $this->generarBaraja();

            // Crear partida
            $partida = Partida::create([
                'sala_id'     => $sala->id,
                'mano_dealer' => [],
                'estado'      => 'en_curso',
            ]);

            // Crear registro por jugador
            foreach ($jugadores as $jugador) {
                PartidaUsuario::create([
                    'partida_id'   => $partida->id,
                    'user_id'      => $jugador->id,
                    'apuesta_total'=> 0,
                    'mano_usuario' => [],
                    'estado'       => 'apostando',
                ]);
            }

            // Actualizar estado de la sala
            $sala->update(['status' => 'playing']);

            // Emitir evento al frontend
            broadcast(new GameStarted($sala, [
                'partida_id'    => $partida->id,
                'jugadores'     => $jugadores->map(fn ($j) => [
                    'id'     => $j->id,
                    'name'   => $j->name,
                    'estado' => 'apostando',
                ]),
                'apuesta_minima' => $ajuste?->apuesta_minima ?? 10,
                'apuesta_maxima' => $ajuste?->apuesta_maxima ?? 1000,
            ]))->toOthers();

            return $partida;
        });
    }

    // ─── Apostar ──────────────────────────────────────────────────

    public function apostar(Partida $partida, User $usuario, int $apuesta): PartidaUsuario
    {
        $ajuste = Ajuste::first();

        if ($apuesta < ($ajuste?->apuesta_minima ?? 10)) {
            throw new \Exception("La apuesta mínima es {$ajuste->apuesta_minima} créditos.");
        }

        if ($apuesta > ($ajuste?->apuesta_maxima ?? 1000)) {
            throw new \Exception("La apuesta máxima es {$ajuste->apuesta_maxima} créditos.");
        }

        $pu = PartidaUsuario::where('partida_id', $partida->id)
            ->where('user_id', $usuario->id)
            ->firstOrFail();

        if ($pu->estado !== 'apostando') {
            throw new \Exception("Ya has apostado o no es el momento de apostar (estado: {$pu->estado}).");
        }

        $pu->update([
            'apuesta_total' => $apuesta,
            'estado'        => 'esperando',
        ]);

        // Descontar la apuesta de la wallet del usuario
        User::where('id', $usuario->id)->decrement('wallet', $apuesta);

        // Si todos han apostado, repartir cartas iniciales
        $pendientes = PartidaUsuario::where('partida_id', $partida->id)
            ->where('estado', 'apostando')
            ->count();

        if ($pendientes === 0) {
            $this->repartirCartasIniciales($partida);
        }

        return $pu->fresh();
    }

    // ─── Repartir cartas iniciales ────────────────────────────────
    // 2 cartas a cada jugador + 2 al dealer (una boca abajo)

    private function repartirCartasIniciales(Partida $partida): void
    {
        $baraja    = $this->generarBaraja();
        $jugadores = $partida->partida_usuarios()->get();

        DB::transaction(function () use ($partida, $jugadores, &$baraja) {
            // Primera carta a cada jugador
            foreach ($jugadores as $pu) {
                $carta = array_shift($baraja);
                $pu->agregarCarta($carta);
                broadcast(new CardDealt($partida->sala, $pu->user_id, $carta))->toOthers();
            }

            // Primera carta al dealer (visible)
            $cartaDealer1 = array_shift($baraja);
            $partida->update(['mano_dealer' => [$cartaDealer1]]);
            broadcast(new CardDealt($partida->sala, null, $cartaDealer1))->toOthers();

            // Segunda carta a cada jugador
            foreach ($jugadores as $pu) {
                $carta = array_shift($baraja);
                $pu->agregarCarta($carta);
                broadcast(new CardDealt($partida->sala, $pu->user_id, $carta))->toOthers();
            }

            // Segunda carta al dealer (boca abajo)
            $cartaDealer2 = array_shift($baraja);
            $manoDealer   = $partida->fresh()->mano_dealer;
            $manoDealer[] = $cartaDealer2;
            $partida->update(['mano_dealer' => $manoDealer]);
            broadcast(new CardDealt($partida->sala, null, $cartaDealer2, true))->toOthers();

            // Detectar blackjacks inmediatos
            foreach ($jugadores as $pu) {
                $pu->refresh();
                if ($pu->tieneBlackjack()) {
                    $pu->update(['estado' => 'finalizado', 'resultado' => 'blackjack']);
                }
            }

            // Pasar turno al primer jugador activo
            $this->siguienteTurno($partida);
        });
    }

    // ─── Pedir carta (hit) ────────────────────────────────────────

    public function hit(Partida $partida, User $usuario): PartidaUsuario
    {
        $pu = $this->obtenerPuActivo($partida, $usuario);

        $carta = $this->generarBaraja()[0]; // carta aleatoria
        $pu->agregarCarta($carta);
        broadcast(new CardDealt($partida->sala, $usuario->id, $carta))->toOthers();

        $pu->refresh();

        if ($pu->haReventado()) {
            $pu->update(['estado' => 'reventado']);
            $this->siguienteTurno($partida);
        }

        return $pu->fresh();
    }

    // ─── Plantarse (stand) ────────────────────────────────────────

    public function stand(Partida $partida, User $usuario): PartidaUsuario
    {
        $pu = $this->obtenerPuActivo($partida, $usuario);
        $pu->update(['estado' => 'plantado']);
        $this->siguienteTurno($partida);

        return $pu->fresh();
    }

    // ─── Doblar (double down) ─────────────────────────────────────
    // Solo con 2 cartas. Se dobla la apuesta, se recibe 1 carta y se planta.

    public function doblar(Partida $partida, User $usuario): PartidaUsuario
    {
        $pu = $this->obtenerPuActivo($partida, $usuario);

        if (count($pu->mano_usuario) !== 2) {
            throw new \Exception('Solo puedes doblar con exactamente 2 cartas.');
        }

        $ajuste = Ajuste::first();
        $nuevaApuesta = $pu->apuesta_total * 2;

        if ($nuevaApuesta > ($ajuste?->apuesta_maxima ?? 1000)) {
            throw new \Exception('La apuesta doblada supera el máximo permitido.');
        }

        // Descontar el extra de la apuesta doblada
        User::where('id', $usuario->id)->decrement('wallet', $pu->apuesta_total);

        $carta = $this->generarBaraja()[0];
        $pu->update(['apuesta_total' => $nuevaApuesta]);
        $pu->agregarCarta($carta);
        broadcast(new CardDealt($partida->sala, $usuario->id, $carta))->toOthers();

        $pu->refresh();
        $estado = $pu->haReventado() ? 'reventado' : 'doblado';
        $pu->update(['estado' => $estado]);

        $this->siguienteTurno($partida);

        return $pu->fresh();
    }

    // ─── Dividir (split) ─────────────────────────────────────────
    // Solo con 2 cartas del mismo valor. Crea una segunda mano.
    // Implementación simplificada: se añade como segundo PartidaUsuario
    // con un flag en la apuesta para identificarlo.

    public function dividir(Partida $partida, User $usuario): array
    {
        $pu = $this->obtenerPuActivo($partida, $usuario);
        $mano = $pu->mano_usuario;

        if (count($mano) !== 2) {
            throw new \Exception('Solo puedes dividir con exactamente 2 cartas.');
        }

        if ($mano[0]['valor'] !== $mano[1]['valor']) {
            throw new \Exception('Solo puedes dividir si las dos cartas tienen el mismo valor.');
        }

        $baraja = $this->generarBaraja();

        DB::transaction(function () use ($pu, $mano, $partida, $usuario, &$baraja) {
            // Mano original: se queda con la primera carta + una nueva
            $carta1 = array_shift($baraja);
            $pu->update([
                'mano_usuario' => [$mano[0], $carta1],
                'estado'       => 'dividido',
            ]);
            broadcast(new CardDealt($partida->sala, $usuario->id, $carta1))->toOthers();

            // Segunda mano: segunda carta original + una nueva
            $carta2 = array_shift($baraja);
            PartidaUsuario::create([
                'partida_id'    => $partida->id,
                'user_id'       => $usuario->id,
                'apuesta_total' => $pu->apuesta_total,
                'mano_usuario'  => [$mano[1], $carta2],
                'estado'        => 'jugando',
            ]);

            // Descontar la apuesta de la segunda mano
            User::where('id', $usuario->id)->decrement('wallet', $pu->apuesta_total);
            broadcast(new CardDealt($partida->sala, $usuario->id, $carta2))->toOthers();
        });

        return PartidaUsuario::where('partida_id', $partida->id)
            ->where('user_id', $usuario->id)
            ->get()
            ->toArray();
    }

    // ─── Turno del dealer y resolución ───────────────────────────

    private function turnoDealer(Partida $partida): void
    {
        $baraja     = $this->generarBaraja();
        $manoDealer = $partida->mano_dealer;

        // Revelar carta oculta
        broadcast(new CardDealt($partida->sala, null, $manoDealer[1]))->toOthers();

        // El dealer pide carta mientras tenga menos de 17
        while (PartidaUsuario::calcularPuntuacion($manoDealer) < 17) {
            $carta       = array_shift($baraja);
            $manoDealer[] = $carta;
            $partida->update(['mano_dealer' => $manoDealer]);
            broadcast(new CardDealt($partida->sala, null, $carta))->toOthers();
        }

        $this->resolverRonda($partida, $manoDealer);
    }

    // ─── Resolver ronda ───────────────────────────────────────────

    private function resolverRonda(Partida $partida, array $manoDealer): void
    {
        $puntuacionDealer = PartidaUsuario::calcularPuntuacion($manoDealer);
        $dealerReventado  = $puntuacionDealer > 21;
        $resultados       = [];

        DB::transaction(function () use ($partida, $puntuacionDealer, $dealerReventado, &$resultados) {
            $jugadores = $partida->partida_usuarios()->get();

            foreach ($jugadores as $pu) {
                $puntuacion = $pu->puntuacion();
                $apuesta    = $pu->apuesta_total;
                $resultado  = $this->determinarResultado(
                    $pu,
                    $puntuacion,
                    $puntuacionDealer,
                    $dealerReventado
                );
                $balance = $this->calcularBalance($resultado, $apuesta);

                $pu->update([
                    'resultado'         => $resultado,
                    'balance_resultado' => $balance,
                    'estado'            => 'finalizado',
                ]);

                // Devolver apuesta + ganancias a la wallet (la apuesta ya fue descontada al apostar)
                // balance: +apuesta (gana), +apuesta*1.5 (blackjack), 0 (empata), -apuesta (pierde)
                // → devolvemos: apuesta + balance (en pierde queda 0; en empate se devuelve la apuesta)
                User::where('id', $pu->user_id)->increment('wallet', $apuesta + $balance);

                // Registrar en tabla manos (resumen contable)
                Mano::create([
                    'sala_id'          => $partida->sala_id,
                    'user_id'          => $pu->user_id,
                    'partida_id'       => $partida->id,
                    'creditos_jugados' => $apuesta,
                    'creditos_ganados' => max(0, $balance + $apuesta),
                ]);

                $resultados[] = [
                    'user_id'          => $pu->user_id,
                    'resultado'        => $resultado,
                    'puntuacion'       => $puntuacion,
                    'balance_resultado'=> $balance,
                ];
            }

            $partida->update(['estado' => 'finalizada']);
            $partida->sala->update(['status' => 'waiting']);
        });

        broadcast(new RoundEnded(
            $partida->sala,
            $resultados,
            $manoDealer
        ))->toOthers();
    }

    private function determinarResultado(
        PartidaUsuario $pu,
        int $puntuacion,
        int $puntuacionDealer,
        bool $dealerReventado
    ): string {
        if ($pu->estado === 'reventado') return 'pierde';
        if ($pu->resultado === 'blackjack') return 'blackjack';
        if ($dealerReventado) return 'gana';
        if ($puntuacion > $puntuacionDealer) return 'gana';
        if ($puntuacion === $puntuacionDealer) return 'empata';
        return 'pierde';
    }

    private function calcularBalance(string $resultado, int $apuesta): int
    {
        return match ($resultado) {
            'blackjack' => (int) round($apuesta * 1.5),  // paga 3:2
            'gana'      => $apuesta,
            'empata'    => 0,
            'pierde'    => -$apuesta,
            default     => 0,
        };
    }

    // ─── Siguiente turno ──────────────────────────────────────────

    private function siguienteTurno(Partida $partida): void
    {
        // Buscar el siguiente jugador en estado 'esperando'
        $siguiente = $partida->partida_usuarios()
            ->where('estado', 'esperando')
            ->orderBy('id')
            ->first();

        if ($siguiente) {
            $siguiente->update(['estado' => 'jugando']);
            broadcast(new TurnChanged(
                $partida->sala,
                $siguiente->user_id,
                30
            ))->toOthers();
            return;
        }

        // No hay más jugadores — turno del dealer
        broadcast(new TurnChanged($partida->sala, null, 0))->toOthers();
        $this->turnoDealer($partida);
    }

    // ─── Helper ───────────────────────────────────────────────────

    private function obtenerPuActivo(Partida $partida, User $usuario): PartidaUsuario
    {
        $pu = PartidaUsuario::where('partida_id', $partida->id)
            ->where('user_id', $usuario->id)
            ->where('estado', 'jugando')
            ->first();

        if (! $pu) {
            throw new \Exception('No es tu turno.');
        }

        return $pu;
    }
}