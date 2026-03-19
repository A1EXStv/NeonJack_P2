<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AjusteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'clave' => $this->clave,
            'valor' => $this->valor,
            'descripcion' => $this->descripcion,
            'created_at' => $this->created_at,
        ];
    }
}   