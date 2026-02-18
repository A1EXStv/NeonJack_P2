<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaccion extends Model{
    use HasFactory;

    protected $table = 'transacciones';
    protected $fillable = ['user_id', 'tipo', 'cantidad'];

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