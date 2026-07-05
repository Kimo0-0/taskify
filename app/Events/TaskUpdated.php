<?php

namespace App\Events;

use App\Models\Task;
use App\Models\CategoryShare;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Task $task)
    {
        $this->task->load(['category', 'subtasks', 'attachments', 'user']);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->task->user_id),
        ];

        // Public channel for users sharing all their tasks (user share token)
        $user = $this->task->user;
        if ($user && $user->share_token) {
            $channels[] = new Channel('shared.user.' . $user->share_token);
        }

        // Public channel for category sharing
        if ($this->task->category_id) {
            $categoryShare = CategoryShare::where('user_id', $this->task->user_id)
                ->where('category_id', $this->task->category_id)
                ->first();
            if ($categoryShare && $categoryShare->share_token) {
                $channels[] = new Channel('shared.category.' . $categoryShare->share_token);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'task' => $this->task->toArray(),
        ];
    }
}
