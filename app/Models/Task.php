<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table = 'tasks';
    protected $fillable = [
      'title',
      'description',
      'due_date',
      'category_id',
      'priority',
      'status',
      'user_id',
      'share_token',
      'share_can_edit',
      'share_can_complete',
    ];

    protected $appends = ['formatted_date', 'category_name', 'share_url'];

    public function getFormattedDateAttribute()
    {
        return \Carbon\Carbon::parse($this->due_date)->format('d M Y');
    }

    public function getShareUrlAttribute()
    {
        return $this->share_token ? url('/shared/task/' . $this->share_token) : null;
    }

    public function getCategoryNameAttribute()
    {
        return $this->category?->name ?? 'no category';
    }

    public function subtasks()
    {
      return $this->hasMany(Subtask::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    protected static function booted()
    {
        static::deleting(function ($task) {
            if ($task->isForceDeleting()) {
                foreach ($task->attachments as $attachment) {
                    \Illuminate\Support\Facades\Storage::delete($attachment->file_path);
                    $attachment->delete();
                }
            }
        });
    }
}
