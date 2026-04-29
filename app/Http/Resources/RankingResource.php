<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingResource extends JsonResource
{
   public function toArray(Request $request): array
{
    $avatar = null;
    if (!empty($this->avatar_file) && !empty($this->avatar_id)) {
        $avatar = asset('storage/' . $this->avatar_id . '/' . $this->avatar_file);
    } else {
        $avatar = asset('images/default.png');
    }

    return [
        'id'     => $this->id,
        'name'   => $this->alias ?? $this->name ?? 'Anónimo',
        'gana'   => $this->gana  ?? null,
        'total'  => $this->total ?? null,
        'avatar' => $avatar,
    ];
}
}