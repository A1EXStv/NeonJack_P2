<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;

    protected $table = 'transacciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'cantidad',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
