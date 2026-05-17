<?php

use App\Models\User;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Category;

test('authenticated user can create a task with subtasks', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $response = $test
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

    $test->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'user_id' => $user->id
    ]);

    $task = Task::firstWhere('title', 'Test Task');
    $test->assertCount(2, $task->subtasks);
});

test('marking a task completed cascades to all its subtasks', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Test Cascade Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    $subtask1 = Subtask::create(['title' => 'Sub1', 'task_id' => $task->id, 'is_completed' => false]);
    $subtask2 = Subtask::create(['title' => 'Sub2', 'task_id' => $task->id, 'is_completed' => false]);

    $response = $test
        ->actingAs($user)
        ->putJson("/tasks/{$task->id}", [
            'status' => 'completed'
        ]);

    $response->assertOk();

    $test->assertTrue((bool) $subtask1->fresh()->is_completed);
    $test->assertTrue((bool) $subtask2->fresh()->is_completed);
});

test('user can soft delete a task', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Test Soft Delete Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    $response = $test
        ->actingAs($user)
        ->deleteJson("/tasks/{$task->id}");

    $response->assertOk();

    $test->assertSoftDeleted('tasks', [
        'id' => $task->id
    ]);
});

test('user cannot view or modify other users tasks', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user1 */
    $user1 = User::factory()->create();
    /** @var \App\Models\User $user2 */
    $user2 = User::factory()->create();

    $taskOwnedBy1 = Task::create([
        'title' => 'Task Owned By 1',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user1->id,
        'status' => 'pending'
    ]);

    $response = $test
        ->actingAs($user2)
        ->deleteJson("/tasks/{$taskOwnedBy1->id}");

    $response->assertStatus(404);
});

test('user can restore a soft deleted task', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Deleted Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);
    $task->delete(); // Soft delete

    $test->assertSoftDeleted('tasks', ['id' => $task->id]);

    $response = $test
        ->actingAs($user)
        ->postJson("/tasks/{$task->id}/restore");

    $response->assertOk();

    $test->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'deleted_at' => null
    ]);
});

test('user can permanently force delete a soft deleted task', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'To Be Permanently Deleted Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);
    $task->delete(); // Soft delete

    $response = $test
        ->actingAs($user)
        ->deleteJson("/tasks/{$task->id}/force-delete");

    $response->assertOk();

    $test->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('user can empty the recycle bin', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    
    $task1 = Task::create(['title' => 'T1', 'due_date' => now()->addDays(1)->toDateTimeString(), 'user_id' => $user->id, 'status' => 'pending']);
    $task2 = Task::create(['title' => 'T2', 'due_date' => now()->addDays(1)->toDateTimeString(), 'user_id' => $user->id, 'status' => 'pending']);
    
    $task1->delete();
    $task2->delete();

    $response = $test
        ->actingAs($user)
        ->postJson("/tasks/empty-trash");

    $response->assertOk();

    $test->assertDatabaseMissing('tasks', ['id' => $task1->id]);
    $test->assertDatabaseMissing('tasks', ['id' => $task2->id]);
});
