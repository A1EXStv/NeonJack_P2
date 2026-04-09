<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mano extends Model
{
    protected $fillable = [
        'sala_id',
        'user_id',
        'partida_id',
        'creditos_jugados',
        'creditos_ganados',
    ];

    protected $casts = [
        'creditos_jugados' => 'integer',
        'creditos_ganados' => 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function neto(): int
    {
        return $this->creditos_ganados - $this->creditos_jugados;
    }
}