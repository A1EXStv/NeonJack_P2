<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingResource extends JsonResource
{
   public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->alias ?? $this->name,
        'wins' => $this->wins ?? null,
        'total' => $this->total ?? null,
        'avatar' => $this->avatar 
            ? asset('storage/' . $this->avatar)
            : asset('images/default.png'),
    ];
}
}