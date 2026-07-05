<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $table = 'attachments';

    protected $fillable = [
        'task_id',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
    ];

    protected $appends = ['file_url', 'formatted_size', 'is_image', 'is_video'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function getFileUrlAttribute()
    {
        return '/storage/' . $this->file_path;
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }

    public function getIsImageAttribute()
    {
        return str_starts_with($this->file_type, 'image/');
    }

    public function getIsVideoAttribute()
    {
        return str_starts_with($this->file_type, 'video/');
    }
}
