<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use App\Events\AttachmentUploaded;
use App\Events\AttachmentDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Upload and store attachments for a task (supports multiple files).
     */
    public function store(Request $request, $taskId)
    {
        $task = Auth::user()->tasks()->findOrFail($taskId);

        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file|max:102400|mimes:jpeg,jpg,png,gif,webp,svg,bmp,mp4,mov,avi,webm,mkv,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
        ]);

        $attachments = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Store file securely inside attachments/{task_id} folder on public disk
            $path = $file->store("attachments/{$task->id}", 'public_direct');

            $attachment = Attachment::create([
                'task_id'   => $task->id,
                'file_path' => $path,
                'file_name' => $originalName,
                'file_size' => $fileSize,
                'file_type' => $mimeType,
            ]);

            broadcast(new AttachmentUploaded($attachment, Auth::id()))->toOthers();

            $attachments[] = $attachment;
        }

        return response()->json([
            'status' => 'success',
            'message' => count($attachments) . ' attachment(s) uploaded successfully',
            'data' => $attachments
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

        if (!Storage::disk('public_direct')->exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        $fullPath = Storage::disk('public_direct')->path($attachment->file_path);

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

        $attachmentId = $attachment->id;
        $taskId = $attachment->task_id;

        // Delete from public storage disk
        if (Storage::disk('public_direct')->exists($attachment->file_path)) {
            Storage::disk('public_direct')->delete($attachment->file_path);
        }

        $attachment->delete();

        broadcast(new AttachmentDeleted($attachmentId, $taskId, Auth::id()))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Attachment deleted successfully'
        ], 200);
    }
}
