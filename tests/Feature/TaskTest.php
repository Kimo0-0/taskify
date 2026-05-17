<?php

use App\Models\User;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Category;

test('authenticated user can create a task with subtasks', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson('/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'due_date' => now()->addDays(2)->toDateTimeString(),
            'priority' => 'high',
            'status' => 'pending',
            'subtasks' => ['Subtask 1', 'Subtask 2']
        ]);

    $response->assertStatus(201);
    
    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'user_id' => $user->id
    ]);

    $task = Task::where('title', 'Test Task')->first();
    $this->assertCount(2, $task->subtasks);
});

test('marking a task completed cascades to all its subtasks', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Test Cascade Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);
    
    $subtask1 = Subtask::create(['title' => 'Sub1', 'task_id' => $task->id, 'is_completed' => false]);
    $subtask2 = Subtask::create(['title' => 'Sub2', 'task_id' => $task->id, 'is_completed' => false]);

    $response = $this
        ->actingAs($user)
        ->putJson("/tasks/{$task->id}", [
            'status' => 'completed'
        ]);

    $response->assertOk();
    
    $this->assertTrue((bool) $subtask1->fresh()->is_completed);
    $this->assertTrue((bool) $subtask2->fresh()->is_completed);
});

test('user can soft delete a task', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Test Soft Delete Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    $response = $this
        ->actingAs($user)
        ->deleteJson("/tasks/{$task->id}");

    $response->assertOk();
    
    $this->assertSoftDeleted('tasks', [
        'id' => $task->id
    ]);
});

test('user cannot view or modify other users tasks', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $taskOwnedBy1 = Task::create([
        'title' => 'Task Owned By 1',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user1->id,
        'status' => 'pending'
    ]);

    $response = $this
        ->actingAs($user2)
        ->deleteJson("/tasks/{$taskOwnedBy1->id}");

    $response->assertStatus(404);
});
