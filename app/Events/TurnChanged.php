<?php

namespace App\Events;

use App\Models\Sala;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Sala $sala,
        public ?int $userId,
        public int $timeout = 30
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('sala.' . $this->sala->id)];
    }

    public function broadcastAs(): string
    {
        return 'TurnChanged';
    }
}
