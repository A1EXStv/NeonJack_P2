<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logro extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'logro_user')
                    ->withPivot('unlocked_at')
                    ->withTimestamps();
    }
}
