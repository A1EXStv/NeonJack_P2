<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartidaUser extends Model
{
    protected $table = 'partida_usuario';

    protected $fillable = [
        'partida_id',
        'user_id',
        'apuesta_total',
        'mano_usuario',
        'resultado',
        'balance_resultado',
        'estado',
    ];

    protected $casts = [
        'mano_usuario'    => 'array',
        'apuesta_total'   => 'integer',
        'balance_resultado' => 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function puntuacion(): int
    {
        return self::calcularPuntuacion($this->mano_usuario ?? []);
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'jugando';
    }

    public function haReventado(): bool
    {
        return $this->puntuacion() > 21;
    }

    public function tieneBlackjack(): bool
    {
        return count($this->mano_usuario ?? []) === 2
            && $this->puntuacion() === 21;
    }

    public function agregarCarta(array $carta): void
    {
        $mano = $this->mano_usuario ?? [];
        $mano[] = $carta;
        $this->update(['mano_usuario' => $mano]);
    }

    // ─── Cálculo de puntuación ────────────────────────────────────
    // Los ases valen 11, pero si la mano supera 21 pasan a valer 1.

    public static function calcularPuntuacion(array $cartas): int
    {
        $total = 0;
        $ases  = 0;

        foreach ($cartas as $carta) {
            $valor = $carta['valor'];

            if (in_array($valor, ['J', 'Q', 'K'])) {
                $total += 10;
            } elseif ($valor === 'A') {
                $total += 11;
                $ases++;
            } else {
                $total += (int) $valor;
            }
        }

        // Reducir ases de 11 a 1 mientras se supere 21
        while ($total > 21 && $ases > 0) {
            $total -= 10;
            $ases--;
        }

        return $total;
    }
}