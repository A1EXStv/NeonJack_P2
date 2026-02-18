<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skin extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'precio',
        'activo',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'skin_user');
    }
}
