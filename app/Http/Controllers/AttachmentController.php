<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Upload and store an attachment for a task.
     */
    public function store(Request $request, $taskId)
    {
        $task = Auth::user()->tasks()->findOrFail($taskId);

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Store file securely inside attachments/{task_id} folder on public disk
        $path = $file->store("attachments/{$task->id}", 'public');

        $attachment = Attachment::create([
            'task_id' => $task->id,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $fileSize,
            'file_type' => $mimeType,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment uploaded successfully',
            'data' => $attachment
        ], 201);
    }

    /**
     * Download an attachment securely.
     */
    public function download($id)
    {
        $attachment = Attachment::findOrFail($id);
        
        // Ensure the task belongs to the authenticated user
        $task = Auth::user()->tasks()->findOrFail($attachment->task_id);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        $fullPath = Storage::disk('public')->path($attachment->file_path);

        return response()->download($fullPath, $attachment->file_name);
    }

    /**
     * Delete an attachment.
     */
    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);
        
        // Ensure the task belongs to the authenticated user
        $task = Auth::user()->tasks()->findOrFail($attachment->task_id);

        // Delete from public storage disk
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment deleted successfully'
        ], 200);
    }
}
