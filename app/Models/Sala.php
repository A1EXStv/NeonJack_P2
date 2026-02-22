<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sala extends Model {
    use HasFactory;

    protected $table = 'salas';
    protected $fillable = ['nombre_sala'];

    // Una sala tiene muchas manos
    public function manos()
    {
        return $this->hasMany(Mano::class);
    }
}