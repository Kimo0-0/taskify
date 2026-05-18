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

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/Dashboard', [TaskController::class, 'index'])->name('dashboard');
  Route::get('/Today', [TaskController::class, 'today'])->name('today');
  Route::get('/Upcoming', [TaskController::class, 'upcoming'])->name('upcoming');
  Route::get('/Important', [TaskController::class, 'important'])->name('important');
  Route::get('/Completed', [TaskController::class, 'completed'])->name('completed');
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.updateProfile');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
  Route::get('/Categories', [CategoryController::class, 'index'])->name('categories.index');
  Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
  Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
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

Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');






require __DIR__.'/auth.php';
