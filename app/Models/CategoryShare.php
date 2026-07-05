<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryShare extends Model
{
    protected $table = 'category_shares';

    protected $fillable = [
        'user_id',
        'category_id',
        'share_token',
        'can_edit',
        'can_complete',
    ];

    protected $appends = ['share_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getShareUrlAttribute()
    {
        return url('/shared/category/' . $this->share_token);
    }
}
