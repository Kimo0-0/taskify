<?php

namespace App\Events;

use App\Models\Attachment;
use App\Models\CategoryShare;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentUploaded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Attachment $attachment, public int $userId)
    {
        $this->attachment->load('task.user');
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->userId),
        ];

        $task = $this->attachment->task;
        if ($task) {
            // User share token
            $user = $task->user;
            if ($user && $user->share_token) {
                $channels[] = new Channel('shared.user.' . $user->share_token);
            }

            // Category share token
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
        return 'attachment.uploaded';
    }

    public function broadcastWith(): array
    {
        return [
            'attachment' => $this->attachment->toArray(),
        ];
    }
}
