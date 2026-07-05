<?php

namespace App\Events;

use App\Models\CategoryShare;
use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $attachmentId,
        public int $taskId,
        public int $userId
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->userId),
        ];

        $task = Task::find($this->taskId);
        if ($task) {
            $user = User::find($this->userId);
            if ($user && $user->share_token) {
                $channels[] = new Channel('shared.user.' . $user->share_token);
            }

            if ($task->category_id) {
                $categoryShare = CategoryShare::where('user_id', $this->userId)
                    ->where('category_id', $task->category_id)
                    ->first();
                if ($categoryShare && $categoryShare->share_token) {
                    $channels[] = new Channel('shared.category.' . $categoryShare->share_token);
                }
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'attachment.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'attachment_id' => $this->attachmentId,
            'task_id'       => $this->taskId,
        ];
    }
}
