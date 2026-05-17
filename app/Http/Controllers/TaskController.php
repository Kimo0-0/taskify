<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use ApiResponse;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display Dashboard or list tasks.
     */
    public function index(Request $request)
    {
        $tasks = $this->taskService->getAllTasks($request->all());

        // Global stats for the user
        $stats = [
            'total' => Auth::user()->tasks()->count(),
            'completed' => Auth::user()->tasks()->where('status', 'completed')->count(),
            'overdue' => Auth::user()->tasks()->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
        ];

        // If it's an AJAX request or expects JSON, return standardized response
        if ($request->wantsJson()) {
            return $this->success(['tasks' => $tasks, 'stats' => $stats], 'Tasks retrieved successfully');
        }

        $categories = \App\Models\Category::all();

        return view('Dashboard', compact('tasks', 'categories', 'stats'));
    }

    /**
     * Store a new task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'required|date',
            'category_id' => 'nullable|exists:categories,id',
            'priority'    => 'nullable|in:low,medium,high',
            'status'      => 'nullable|in:pending,in progress,completed',
            'subtasks'    => 'nullable|array',
            'subtasks.*'  => 'string|max:255',
        ]);

        try {
            $task = $this->taskService->createTask($validated);
            return $this->success($task, 'Task created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create task', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, $id)
    {
        $task = Auth::user()->tasks()->find($id);

        if (!$task) {
            return $this->error('Task not found', 404);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'sometimes|date',
            'category_id' => 'nullable|exists:categories,id',
            'priority'    => 'sometimes|in:low,medium,high',
            'status'      => 'sometimes|in:pending,in progress,completed',
            'subtasks'    => 'nullable|array',
            'subtasks.*'  => 'string|max:255',
        ]);

        try {
            $updatedTask = $this->taskService->updateTask($task, $validated);
            return $this->success($updatedTask, 'Task updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update task', 500, [$e->getMessage()]);
        }
    }

    /**
     * Delete a task (Soft Delete).
     */
    public function destroy($id)
    {
        $task = Auth::user()->tasks()->find($id);

        if (!$task) {
            return $this->error('Task not found', 404);
        }

        try {
            $this->taskService->deleteTask($task);
            return $this->success(null, 'Task deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete task', 500, [$e->getMessage()]);
        }
    }

    /**
     * Show soft-deleted tasks.
     */
    public function trash()
    {
        $tasks = Auth::user()->tasks()->onlyTrashed()->with(['category', 'subtasks'])->orderBy('deleted_at', 'desc')->paginate(10);
        $categories = \App\Models\Category::all();
        $title = 'Recycle Bin';
        $activeNav = 'Trash_nav';

        $stats = [
            'total' => Auth::user()->tasks()->count(),
            'completed' => Auth::user()->tasks()->where('status', 'completed')->count(),
            'overdue' => Auth::user()->tasks()->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
        ];

        return view('Dashboard', compact('tasks', 'categories', 'title', 'activeNav', 'stats'));
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore($id)
    {
        $task = Auth::user()->tasks()->onlyTrashed()->find($id);

        if (!$task) {
            return $this->error('Task not found in trash', 404);
        }

        $task->restore();

        return $this->success(null, 'Task restored successfully');
    }

    /**
     * Permanently delete a task.
     */
    public function forceDelete($id)
    {
        $task = Auth::user()->tasks()->onlyTrashed()->find($id);

        if (!$task) {
            return $this->error('Task not found in trash', 404);
        }

        $task->subtasks()->delete();
        $task->forceDelete();

        return $this->success(null, 'Task permanently deleted');
    }

    /**
     * Show tasks for Today.
     */
    public function today()
    {
        $tasks = $this->taskService->getTodayTasks();
        $categories = \App\Models\Category::all();
        $title = 'Today\'s Tasks';
        $activeNav = 'Today_nav';

        $stats = [
            'total' => Auth::user()->tasks()->count(),
            'completed' => Auth::user()->tasks()->where('status', 'completed')->count(),
            'overdue' => Auth::user()->tasks()->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
        ];

        return view('Dashboard', compact('tasks', 'categories', 'title', 'activeNav', 'stats'));
    }

    /**
     * Show Upcoming tasks.
     */
    public function upcoming()
    {
        $tasks = $this->taskService->getUpcomingTasks();
        $categories = \App\Models\Category::all();
        $title = 'Upcoming Tasks';
        $activeNav = 'Upcoming_nav';

        $stats = [
            'total' => Auth::user()->tasks()->count(),
            'completed' => Auth::user()->tasks()->where('status', 'completed')->count(),
            'overdue' => Auth::user()->tasks()->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
        ];

        return view('Dashboard', compact('tasks', 'categories', 'title', 'activeNav', 'stats'));
    }

    /**
     * Show Important tasks.
     */
    public function important()
    {
        $tasks = $this->taskService->getImportantTasks();
        $categories = \App\Models\Category::all();
        $title = 'Important Tasks';
        $activeNav = 'Important_nav';

        $stats = [
            'total' => Auth::user()->tasks()->count(),
            'completed' => Auth::user()->tasks()->where('status', 'completed')->count(),
            'overdue' => Auth::user()->tasks()->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
        ];

        return view('Dashboard', compact('tasks', 'categories', 'title', 'activeNav', 'stats'));
    }

    /**
     * Show Completed tasks.
     */
    public function completed()
    {
        $tasks = $this->taskService->getCompletedTasks();
        $categories = \App\Models\Category::all();
        $title = 'Completed Tasks';
        $activeNav = 'Completed_nav';

        $stats = [
            'total' => Auth::user()->tasks()->count(),
            'completed' => Auth::user()->tasks()->where('status', 'completed')->count(),
            'overdue' => Auth::user()->tasks()->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
        ];

        return view('Dashboard', compact('tasks', 'categories', 'title', 'activeNav', 'stats'));
    }

    /**
     * Show task details view.
     */
    public function show($id)
    {
        $task = Auth::user()->tasks()->with(['category', 'subtasks'])->find($id);

        if (!$task) {
            abort(404);
        }

        return view('task', compact('task'));
    }
}
