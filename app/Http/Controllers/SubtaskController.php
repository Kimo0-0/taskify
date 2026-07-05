<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Events\SubtaskToggled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubtaskController extends Controller
{
    public function toggle($id)
    {
        $subtask = Subtask::findOrFail($id);
        $subtask->is_completed = !$subtask->is_completed;
        $subtask->save();

        // Check if all subtasks for the parent task are completed
        $task = $subtask->task;
        $totalSubtasks = $task->subtasks()->count();
        $completedSubtasks = $task->subtasks()->where('is_completed', true)->count();

        if ($totalSubtasks > 0) {
            if ($completedSubtasks === $totalSubtasks) {
                $task->status = 'completed';
            } else {
                $task->status = 'pending';
            }
            $task->save();
        }

        broadcast(new SubtaskToggled($subtask, $task->user_id))->toOthers();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $subtask->id,
                'is_completed' => $subtask->is_completed,
                'task_id' => $subtask->task_id,
                'task_status' => $task->status
            ]
        ]);
    }
}
