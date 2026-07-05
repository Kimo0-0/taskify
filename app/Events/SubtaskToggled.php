<?php

namespace App\Events;

use App\Models\Subtask;
use App\Models\CategoryShare;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubtaskToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Subtask $subtask, public int $userId)
    {
        $this->subtask->load('task');
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->userId),
        ];

        // Public channel for users sharing all their tasks
        $user = User::find($this->userId);
        if ($user && $user->share_token) {
            $channels[] = new Channel('shared.user.' . $user->share_token);
        }

        // Public channel for category sharing
        if ($this->subtask->task && $this->subtask->task->category_id) {
            $categoryShare = CategoryShare::where('user_id', $this->userId)
                ->where('category_id', $this->subtask->task->category_id)
                ->first();
            if ($categoryShare && $categoryShare->share_token) {
                $channels[] = new Channel('shared.category.' . $categoryShare->share_token);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'subtask.toggled';
    }

    public function broadcastWith(): array
    {
        return [
            'subtask' => $this->subtask->toArray(),
        ];
    }
}
