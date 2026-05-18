<?php

use App\Models\User;
use App\Models\Task;
use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can upload an attachment to their task', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    
    $task = Task::create([
        'title' => 'Attach Test Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    $fakeDisk = Storage::fake('public');

    $file = UploadedFile::fake()->image('document.jpg');

    $response = $test
        ->actingAs($user)
        ->postJson("/tasks/{$task->id}/attachments", [
            'file' => $file
        ]);

    $response->assertCreated();
    $test->assertDatabaseHas('attachments', [
        'task_id' => $task->id,
        'file_name' => 'document.jpg'
    ]);

    $attachment = Attachment::where('task_id', $task->id)->first();
    $fakeDisk->assertExists($attachment->file_path);
});

test('user cannot upload attachments exceeding size limit', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    
    $task = Task::create([
        'title' => 'Attach Test Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    Storage::fake('public'); // size-limit test — no disk assertions needed

    // Create a fake file larger than 10MB (11MB)
    $file = UploadedFile::fake()->create('heavy.pdf', 11 * 1024);

    $response = $test
        ->actingAs($user)
        ->postJson("/tasks/{$task->id}/attachments", [
            'file' => $file
        ]);

    $response->assertStatus(422);
});

test('user can download an attachment belonging to their task', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    
    $task = Task::create([
        'title' => 'Attach Test Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    $fakeDisk = Storage::fake('public');
    $path = $fakeDisk->putFile("attachments/{$task->id}", UploadedFile::fake()->image('photo.png'));

    $attachment = Attachment::create([
        'task_id' => $task->id,
        'file_path' => $path,
        'file_name' => 'photo.png',
        'file_size' => 1024,
        'file_type' => 'image/png'
    ]);

    $response = $test
        ->actingAs($user)
        ->get("/attachments/{$attachment->id}/download");

    $response->assertOk();
    $response->assertHeader('Content-Disposition', 'attachment; filename=photo.png');
});

test('user can delete an attachment', function () {
    /** @var \Tests\TestCase $test */
    $test = $this;
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    
    $task = Task::create([
        'title' => 'Attach Test Task',
        'due_date' => now()->addDays(1)->toDateTimeString(),
        'user_id' => $user->id,
        'status' => 'pending'
    ]);

    $fakeDisk = Storage::fake('public');
    $path = $fakeDisk->putFile("attachments/{$task->id}", UploadedFile::fake()->image('todelete.png'));

    $attachment = Attachment::create([
        'task_id' => $task->id,
        'file_path' => $path,
        'file_name' => 'todelete.png',
        'file_size' => 1024,
        'file_type' => 'image/png'
    ]);

    $response = $test
        ->actingAs($user)
        ->deleteJson("/attachments/{$attachment->id}");

    $response->assertOk();
    $test->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    $fakeDisk->assertMissing($path);
});
