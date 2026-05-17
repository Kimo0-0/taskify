<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Models\Task;
use App\Models\User;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubtaskController;

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
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
  Route::get('/Categories', [CategoryController::class, 'index'])->name('categories.index');
  Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
  Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
  Route::get('/Trash', [TaskController::class, 'trash'])->name('trash');
  Route::get('/tasks/api-search', [TaskController::class, 'apiSearch'])->name('tasks.apiSearch');
  Route::post('/tasks/empty-trash', [TaskController::class, 'emptyTrash'])->name('tasks.emptyTrash');
  Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
  Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
  Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
  Route::post('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
  Route::delete('/tasks/{id}/force-delete', [TaskController::class, 'forceDelete'])->name('tasks.forceDelete');
  Route::post('/subtasks/{id}/toggle', [SubtaskController::class, 'toggle'])->name('subtasks.toggle');
  Route::get('/task/{id}', function ($id) {$task = Task::findOrFail($id);return view('task', ['task' => $task]);});
});

Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');






require __DIR__.'/auth.php';
