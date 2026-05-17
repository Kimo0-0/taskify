<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'puzzles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fen',
        'pgn',
        'type',
    ];
}
?>
