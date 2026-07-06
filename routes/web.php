<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Models\Task;
use App\Models\User;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\AttachmentController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});



// Fallback route to serve storage files if symlink fails (useful for Windows environments)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    // Guess mime type for video streaming support
    $mimeType = mime_content_type($filePath);
    $headers = ['Content-Type' => $mimeType];
    
    return response()->file($filePath, $headers);
})->where('path', '.*');

use App\Http\Controllers\PublicShareController;

// Public guest routes for sharing
Route::get('/shared/task/{token}', [PublicShareController::class, 'showTask'])->name('shared.task');
Route::get('/shared/category/{token}', [PublicShareController::class, 'showCategory'])->name('shared.category');
Route::get('/shared/category/{categoryToken}/task/{taskId}', [PublicShareController::class, 'showCategoryTask'])->name('shared.category.task');
Route::get('/shared/user/{token}', [PublicShareController::class, 'showUserTasks'])->name('shared.user.tasks');
Route::get('/shared/user/{userToken}/task/{taskId}', [PublicShareController::class, 'showUserTask'])->name('shared.user.task');
Route::post('/shared/task/{id}/toggle/{token}', [PublicShareController::class, 'toggleTaskStatus'])->name('shared.task.toggle');
Route::post('/shared/subtask/{id}/toggle/{token}', [PublicShareController::class, 'toggleSubtaskStatus'])->name('shared.subtask.toggle');
Route::post('/shared/task/{id}/update/{token}', [PublicShareController::class, 'updateTaskDetails'])->name('shared.task.update');
Route::get('/shared/attachment/{id}/{token}', [PublicShareController::class, 'downloadAttachment'])->name('shared.attachment.download');

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/Dashboard', [TaskController::class, 'index'])->name('dashboard');
  Route::get('/Today', [TaskController::class, 'today'])->name('today');
  Route::get('/Upcoming', [TaskController::class, 'upcoming'])->name('upcoming');
  Route::get('/Important', [TaskController::class, 'important'])->name('important');
  Route::get('/Completed', [TaskController::class, 'completed'])->name('completed');
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.updateProfile');
  Route::post('/profile/share', [ProfileController::class, 'toggleShare'])->name('profile.share');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
  Route::get('/Categories', [CategoryController::class, 'index'])->name('categories.index');
  Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
  Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
  Route::post('/categories/{id}/share', [CategoryController::class, 'toggleShare'])->name('categories.share');
  Route::get('/Trash', [TaskController::class, 'trash'])->name('trash');
  Route::get('/tasks/api-search', [TaskController::class, 'apiSearch'])->name('tasks.apiSearch');
  Route::post('/tasks/bulk-delete', [TaskController::class, 'bulkDelete'])->name('tasks.bulkDelete');
  Route::post('/tasks/bulk-restore', [TaskController::class, 'bulkRestore'])->name('tasks.bulkRestore');
  Route::post('/tasks/bulk-force-delete', [TaskController::class, 'bulkForceDelete'])->name('tasks.bulkForceDelete');
  Route::post('/tasks/empty-trash', [TaskController::class, 'emptyTrash'])->name('tasks.emptyTrash');
  Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
  Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
  Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
  Route::post('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
  Route::delete('/tasks/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');
  Route::post('/tasks/{id}/share', [TaskController::class, 'toggleShare'])->name('tasks.share');
  Route::post('/subtasks/{id}/toggle', [SubtaskController::class, 'toggle'])->name('subtasks.toggle');
  
  // Task Attachment Routes
  Route::post('/tasks/{id}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
  Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
  Route::get('/attachments/{id}/download', [AttachmentController::class, 'download'])->name('attachments.download');

  Route::get('/task/{id}', function ($id) {
      // Load task with subtasks and attachments
      $task = Task::with(['subtasks', 'attachments'])->findOrFail($id);
      return view('task', ['task' => $task]);
  });
});





require __DIR__.'/auth.php';
