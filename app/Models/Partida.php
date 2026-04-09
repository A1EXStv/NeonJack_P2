<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PartidaUser as PartidaUsuario;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Partida extends Model
{
    protected $fillable = [
        'sala_id',
        'mano_dealer',
        'estado',
    ];

    protected $casts = [
        'mano_dealer' => 'array',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function jugadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'partida_usuario')
            ->withPivot([
                'apuesta_total',
                'mano_usuario',
                'resultado',
                'balance_resultado',
                'estado',
            ])
            ->withTimestamps();
    }

    public function partida_usuarios(): HasMany
    {
        return $this->hasMany(PartidaUsuario::class);
    }

    public function manos(): HasMany
    {
        return $this->hasMany(Mano::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function estaEnCurso(): bool
    {
        return $this->estado === 'en_curso';
    }

    public function puntuacionDealer(): int
    {
        return PartidaUsuario::calcularPuntuacion($this->mano_dealer ?? []);
    }

    public function jugadorActual(): ?PartidaUsuario
    {
        return $this->partida_usuarios()
            ->where('estado', 'jugando')
            ->first();
    }

    public function todosHanJugado(): bool
    {
        return ! $this->partida_usuarios()
            ->whereIn('estado', ['esperando', 'apostando', 'jugando'])
            ->exists();
    }
}