<?php

namespace app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cartera extends Model{
    use HasFactory;

    protected $table = 'carteras';
    protected $fillable = ['user_id', 'cantidad', 'tipoMovimiento', 'concepto'];

  

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}