<?php

namespace App\Events;

use App\Models\CategoryShare;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $taskId,
        public int $userId,
        public ?int $categoryId = null,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->userId),
        ];

        // Public channel for users sharing all their tasks (user share token)
        $user = User::find($this->userId);
        if ($user && $user->share_token) {
            $channels[] = new Channel('shared.user.' . $user->share_token);
        }

        // Public channel for category sharing
        if ($this->categoryId) {
            $categoryShare = CategoryShare::where('user_id', $this->userId)
                ->where('category_id', $this->categoryId)
                ->first();
            if ($categoryShare && $categoryShare->share_token) {
                $channels[] = new Channel('shared.category.' . $categoryShare->share_token);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'task.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->taskId,
        ];
    }
}
