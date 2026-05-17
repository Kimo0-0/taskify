<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    /**
     * Get all tasks for the authenticated user with search and filters.
     */
    public function getAllTasks(array $filters = [])
    {
        $query = Auth::user()->tasks()->with(['category', 'subtasks']);

        // Search by title or description
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by priority
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Sort and Paginate
        return $query->orderBy('due_date', 'asc')->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Create a new task.
     */
    public function createTask(array $data)
    {
        $task = Auth::user()->tasks()->create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date'    => $data['due_date'],
            'category_id' => $data['category_id'] ?? null,
            'priority'    => $data['priority'] ?? 'medium',
            'status'      => $data['status'] ?? 'pending',
        ]);

        if (isset($data['subtasks']) && is_array($data['subtasks'])) {
            foreach ($data['subtasks'] as $subtaskTitle) {
                $task->subtasks()->create(['title' => $subtaskTitle]);
            }
        }

        return $task->load(['category', 'subtasks']);
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, array $data)
    {
        $task->update($data);

        if (isset($data['subtasks']) && is_array($data['subtasks'])) {
            $task->subtasks()->delete();
            foreach ($data['subtasks'] as $subtaskTitle) {
                $task->subtasks()->create(['title' => $subtaskTitle]);
            }
        }

        return $task->load(['category', 'subtasks']);
    }

    /**
     * Delete a task (Soft Delete).
     */
    public function deleteTask(Task $task)
    {
        return $task->delete();
    }

    /**
     * Get tasks for Today.
     */
    public function getTodayTasks()
    {
        return Auth::user()->tasks()
            ->with(['category', 'subtasks'])
            ->whereDate('due_date', \Carbon\Carbon::today())
            ->orderBy('due_date', 'asc')
            ->paginate(10);
    }

    /**
     * Get Upcoming tasks.
     */
    public function getUpcomingTasks()
    {
        return Auth::user()->tasks()
            ->with(['category', 'subtasks'])
            ->whereDate('due_date', '>', \Carbon\Carbon::today())
            ->orderBy('due_date', 'asc')
            ->paginate(10);
    }

    /**
     * Get Important tasks (High priority).
     */
    public function getImportantTasks()
    {
        return Auth::user()->tasks()
            ->with(['category', 'subtasks'])
            ->where('priority', 'high')
            ->orderBy('due_date', 'asc')
            ->paginate(10);
    }

    /**
     * Get Completed tasks.
     */
    public function getCompletedTasks()
    {
        return Auth::user()->tasks()
            ->with(['category', 'subtasks'])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    }
}
