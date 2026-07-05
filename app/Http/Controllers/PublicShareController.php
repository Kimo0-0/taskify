<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\CategoryShare;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicShareController extends Controller
{
    /**
     * View a shared task details page publicly.
     */
    public function showTask($token)
    {
        $task = Task::where('share_token', $token)
            ->with(['subtasks', 'attachments', 'category', 'user'])
            ->firstOrFail();

        return view('shared.task', [
            'task'              => $task,
            'token'             => $token,
            'isCategoryContext' => false,
            'canComplete'       => (bool) $task->share_can_complete,
            'canEdit'           => (bool) $task->share_can_edit,
        ]);
    }

    /**
     * View a shared category (folder) publicly.
     */
    public function showCategory($token)
    {
        $share = CategoryShare::where('share_token', $token)
            ->with(['category', 'user'])
            ->firstOrFail();

        $tasks = Task::where('user_id', $share->user_id)
            ->where('category_id', $share->category_id)
            ->where('status', '!=', 'completed')
            ->with(['category', 'subtasks'])
            ->orderBy('due_date', 'asc')
            ->get();

        $completedTasks = Task::where('user_id', $share->user_id)
            ->where('category_id', $share->category_id)
            ->where('status', 'completed')
            ->with(['category', 'subtasks'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('shared.category', [
            'share'          => $share,
            'tasks'          => $tasks,
            'completedTasks' => $completedTasks,
            'token'          => $token,
            'canComplete'    => (bool) $share->can_complete,
            'canEdit'        => (bool) $share->can_edit,
        ]);
    }

    /**
     * View a task inside a shared category publicly.
     */
    public function showCategoryTask($categoryToken, $taskId)
    {
        $share = CategoryShare::where('share_token', $categoryToken)->firstOrFail();

        $task = Task::where('id', $taskId)
            ->where('user_id', $share->user_id)
            ->where('category_id', $share->category_id)
            ->with(['subtasks', 'attachments', 'category', 'user'])
            ->firstOrFail();

        return view('shared.task', [
            'task'              => $task,
            'token'             => $categoryToken,
            'isCategoryContext' => true,
            'canComplete'       => (bool) $share->can_complete,
            'canEdit'           => (bool) $share->can_edit,
        ]);
    }

    /**
     * View all tasks of a shared user publicly.
     */
    public function showUserTasks($token)
    {
        $user = \App\Models\User::where('share_token', $token)->firstOrFail();

        $tasks = Task::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->with(['category', 'subtasks'])
            ->orderBy('due_date', 'asc')
            ->get();

        $completedTasks = Task::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['category', 'subtasks'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('shared.user_tasks', [
            'user'           => $user,
            'tasks'          => $tasks,
            'completedTasks' => $completedTasks,
            'token'          => $token,
            'canComplete'    => (bool) $user->share_can_complete,
            'canEdit'        => (bool) $user->share_can_edit,
        ]);
    }

    /**
     * View a task inside a user's shared tasks publicly.
     */
    public function showUserTask($userToken, $taskId)
    {
        $user = \App\Models\User::where('share_token', $userToken)->firstOrFail();

        $task = Task::where('id', $taskId)
            ->where('user_id', $user->id)
            ->with(['subtasks', 'attachments', 'category', 'user'])
            ->firstOrFail();

        return view('shared.task', [
            'task'              => $task,
            'token'             => $userToken,
            'isCategoryContext' => false,
            'isUserContext'     => true,
            'userToken'         => $userToken,
            'canComplete'       => (bool) $user->share_can_complete,
            'canEdit'           => (bool) $user->share_can_edit,
        ]);
    }

    /**
     * Download attachment publicly for guests, validating that the attachment
     * is part of a shared task, shared category, or shared user.
     */
    public function downloadAttachment($id, $token)
    {
        $attachment = Attachment::findOrFail($id);
        $authorized = false;

        // 1. Try task share_token
        $task = Task::where('id', $attachment->task_id)
            ->where('share_token', $token)
            ->first();

        if ($task) {
            $authorized = true;
        } else {
            // 2. Try category share_token
            $share = CategoryShare::where('share_token', $token)->first();
            if ($share) {
                $task = Task::where('id', $attachment->task_id)
                    ->where('user_id', $share->user_id)
                    ->where('category_id', $share->category_id)
                    ->first();
                if ($task) {
                    $authorized = true;
                }
            } else {
                // 3. Try user share_token
                $user = \App\Models\User::where('share_token', $token)->first();
                if ($user) {
                    $task = Task::where('id', $attachment->task_id)
                        ->where('user_id', $user->id)
                        ->first();
                    if ($task) {
                        $authorized = true;
                    }
                }
            }
        }

        if (!$authorized) {
            abort(403, 'Unauthorized access to this attachment.');
        }

        if (!Storage::disk('public_direct')->exists($attachment->file_path)) {
            abort(404, 'File not found on server.');
        }

        $fullPath = Storage::disk('public_direct')->path($attachment->file_path);

        return response()->download($fullPath, $attachment->file_name);
    }

    /**
     * Validate permission for public shared actions.
     */
    private function validatePermission($taskId, $token, $permissionType)
    {
        $task = Task::findOrFail($taskId);
        
        // 1. Try task share_token
        if ($task->share_token === $token) {
            if ($permissionType === 'edit' && $task->share_can_edit) {
                return $task;
            }
            if ($permissionType === 'complete' && $task->share_can_complete) {
                return $task;
            }
        }

        // 2. Try category share_token
        $share = CategoryShare::where('share_token', $token)->first();
        if ($share && $task->user_id === $share->user_id && $task->category_id === $share->category_id) {
            if ($permissionType === 'edit' && $share->can_edit) {
                return $task;
            }
            if ($permissionType === 'complete' && $share->can_complete) {
                return $task;
            }
        }

        // 3. Try user share_token
        $user = \App\Models\User::where('share_token', $token)->first();
        if ($user && $task->user_id === $user->id) {
            if ($permissionType === 'edit' && $user->share_can_edit) {
                return $task;
            }
            if ($permissionType === 'complete' && $user->share_can_complete) {
                return $task;
            }
        }

        abort(403, 'You do not have permission to perform this action.');
    }

    /**
     * Toggle a task complete status publicly.
     */
    public function toggleTaskStatus(Request $request, $id, $token)
    {
        $task = $this->validatePermission($id, $token, 'complete');

        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();

        return response()->json([
            'status' => 'success',
            'data' => $task,
            'message' => 'Task status updated'
        ]);
    }

    /**
     * Toggle a subtask complete status publicly.
     */
    public function toggleSubtaskStatus(Request $request, $subtaskId, $token)
    {
        $subtask = \App\Models\Subtask::findOrFail($subtaskId);
        $task = $this->validatePermission($subtask->task_id, $token, 'complete');

        $subtask->is_completed = !$subtask->is_completed;
        $subtask->save();

        return response()->json([
            'status' => 'success',
            'data' => $subtask,
            'message' => 'Subtask status updated'
        ]);
    }

    /**
     * Edit a task publicly.
     */
    public function updateTaskDetails(Request $request, $id, $token)
    {
        $task = $this->validatePermission($id, $token, 'edit');

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'required|date',
            'priority'    => 'required|in:low,medium,high',
        ]);

        $task->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $task,
            'message' => 'Task details updated'
        ]);
    }
}
