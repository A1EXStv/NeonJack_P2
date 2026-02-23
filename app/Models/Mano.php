<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mano extends Model {
    use HasFactory;

    protected $table = 'manos';
    protected $fillable = ['user_id', 'creditos_jugados', 'creditos_ganados', 'sala_id'];

    // Una mano pertenece a una sala
    public function sala()
    {
        return $this->belongsTo(Sala::class); 
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }
}