<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;
    protected $fillable = [
        'chapter_id',
        'title',
        'content',
        'order',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
