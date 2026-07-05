<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
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
                // Get tasks for dashboard, excluding completed ones unless the user is on the Completed page
        if ($request->filled('active_nav') && $request->active_nav === 'Completed_nav') {
            $tasks = Auth::user()->tasks()->with(['category', 'subtasks'])->orderBy('updated_at', 'desc')->paginate(10);
        } else {
            $tasks = Auth::user()->tasks()->where('status', '!=', 'completed')->with(['category', 'subtasks'])->orderBy('due_date', 'asc')->paginate(10);
        }

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
            broadcast(new TaskCreated($task))->toOthers();
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
            broadcast(new TaskUpdated($updatedTask))->toOthers();
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
            $taskId = $task->id;
            $userId = $task->user_id;
            $categoryId = $task->category_id;
            $this->taskService->deleteTask($task);
            broadcast(new TaskDeleted($taskId, $userId, $categoryId))->toOthers();
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
     * API Endpoint for unpaginated real-time search & multi-filtering.
     */
    public function apiSearch(Request $request)
    {
        $query = Auth::user()->tasks()->with(['category', 'subtasks']);

        // Check if we are searching within Trashed tasks
        if ($request->active_nav === 'Trash_nav') {
            $query->onlyTrashed();
        } else {
            // Apply standard scopes based on the active page filter
            if ($request->active_nav === 'Today_nav') {
                $query->whereDate('due_date', \Carbon\Carbon::today());
            } elseif ($request->active_nav === 'Important_nav') {
                $query->where('priority', 'high');
            } elseif ($request->active_nav === 'Completed_nav') {
                $query->where('status', 'completed');
            }
        }

        // Strict exclusion of completed tasks from non-completed and non-trash navigations
        if ($request->active_nav !== 'Completed_nav' && $request->active_nav !== 'Trash_nav') {
            $query->where('status', '!=', 'completed');
        }

        // Apply Fuzzy Search Term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        // Apply Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Fetch all matching tasks with pagination (10 per page)
        $tasks = $query->orderBy('due_date', 'asc')->paginate(10);

        // Format to match exact response expectation of buildTaskHtml
        $formattedTasks = collect($tasks->items())->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'due_date' => $task->due_date,
                'status' => $task->status,
                'priority' => $task->priority,
                'category_id' => $task->category_id,
                'category_name' => $task->category_name,
                'formatted_date' => \Carbon\Carbon::parse($task->due_date)->format('M d, Y h:i A'),
                'share_token' => $task->share_token,
                'share_url' => $task->share_url,
                'subtasks' => $task->subtasks->map(function ($subtask) {
                    return [
                        'id' => $subtask->id,
                        'title' => $subtask->title,
                        'is_completed' => (bool)$subtask->is_completed,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'prev_page_url' => $tasks->previousPageUrl(),
            'next_page_url' => $tasks->nextPageUrl(),
            'data' => $formattedTasks
        ]);
    }

    /**
     * Soft delete multiple tasks.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tasks,id'
        ]);

        $deletedCount = Auth::user()->tasks()->whereIn('id', $request->ids)->delete();

        return $this->success(null, "$deletedCount tasks moved to trash");
    }

    /**
     * Restore multiple soft-deleted tasks from the recycle bin.
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        $restoredCount = Auth::user()->tasks()->onlyTrashed()->whereIn('id', $request->ids)->restore();

        return $this->success(null, "$restoredCount tasks restored successfully");
    }

    /**
     * Permanently delete multiple soft-deleted tasks from the database.
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        $tasks = Auth::user()->tasks()->onlyTrashed()->whereIn('id', $request->ids)->get();
        foreach ($tasks as $task) {
            $task->subtasks()->delete();
            $task->forceDelete();
        }

        return $this->success(null, count($tasks) . " tasks permanently deleted");
    }

    /**
     * Permanently delete all soft-deleted tasks for the user.
     */
    public function emptyTrash()
    {
        try {
            $tasks = Auth::user()->tasks()->onlyTrashed()->get();
            foreach ($tasks as $task) {
                $task->subtasks()->delete();
                $task->forceDelete();
            }
            return $this->success(null, 'Recycle bin emptied successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to empty recycle bin', 500, [$e->getMessage()]);
        }
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

    /**
     * Toggle public sharing on/off for a task, and update permissions.
     */
    public function toggleShare(Request $request, $id)
    {
        $task = Auth::user()->tasks()->findOrFail($id);

        if ($request->has('disable') && $request->disable) {
            $task->share_token = null;
            $task->share_can_edit = false;
            $task->share_can_complete = false;
            $task->save();
            return response()->json([
                'status' => 'success',
                'shared' => false,
                'message' => 'Sharing disabled successfully'
            ]);
        }

        if ($task->share_token && !$request->has('share_can_edit') && !$request->has('share_can_complete')) {
            $task->share_token = null;
            $task->share_can_edit = false;
            $task->share_can_complete = false;
            $task->save();
            return response()->json([
                'status' => 'success',
                'shared' => false,
                'message' => 'Sharing disabled successfully'
            ]);
        }

        if (!$task->share_token) {
            $task->share_token = \Illuminate\Support\Str::random(32);
        }

        if ($request->has('share_can_edit')) {
            $task->share_can_edit = (bool)$request->share_can_edit;
        }
        if ($request->has('share_can_complete')) {
            $task->share_can_complete = (bool)$request->share_can_complete;
        }

        $task->save();

        return response()->json([
            'status' => 'success',
            'shared' => true,
            'share_url' => $task->share_url,
            'share_can_edit' => (bool)$task->share_can_edit,
            'share_can_complete' => (bool)$task->share_can_complete,
            'message' => 'Share settings updated successfully'
        ]);
    }
}
