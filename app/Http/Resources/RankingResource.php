<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingResource extends JsonResource
{
public function toArray(Request $request): array
{
    $user = \App\Models\User::find($this->id);

    return [
        'id'     => $this->id,
        'name'   => $this->alias ?? $this->name,
        'gana'   => $this->gana  ?? null,
        'total'  => $this->total ?? null,
        'avatar' => $user?->getFirstMediaUrl('images-users') ?: asset('images/default.png'),
    ];
}

}