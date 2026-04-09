<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Sala extends Model {
    use HasFactory;

    protected $table = 'salas';
    protected $fillable = [
        'nombre_sala',
        'code',
        'status', 
        'max_players',
        'owner_id'
    ];
    protected $casts = [
        'max_players' => 'integer',
    ];

    // RELACIONES BDD

    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function players(){
        return $this->belongsToMany(User::class, 'sala_usuario')->withPivot(['status', 'chips', 'seat'])->withTimestamps();
    }
 
    public function activePlayers(){
        return $this->players()->wherePivotIn('status', ['sitting', 'ready', 'playing']);
    }

    public function manos(){
        return $this->hasMany(Mano::class);
    }

     //AYUDAS
 
    public function isFull(): bool
    {
        return $this->activePlayers()->count() >= $this->max_players;
    }
 
    public function hasPlayer(int $userId): bool
    {
        return $this->players()->where('user_id', $userId)->exists();
    }
 
    public function availableSeat(): ?int
    {
        $taken = $this->activePlayers()->pluck('sala_usuario.seat')->filter()->toArray();
        for ($i = 1; $i <= $this->max_players; $i++) {
            if (! in_array($i, $taken)) {
                return $i;
            }
        }
        return null;
    }
 
    public function canStart(): bool
    {
        return $this->status === 'waiting'
            && $this->activePlayers()->count() >= 1;
    }
 
    //Generar código único automáticamente
 
    protected static function booted(): void
    {
        static::creating(function (Sala $sala) {
            $sala->code = strtoupper('BJ-' . Str::random(4));
        });
    }
}