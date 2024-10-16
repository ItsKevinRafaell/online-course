<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;
    protected $table = 'chapters';

    protected $fillable = [
        'series_id',
        'title',
        'order',
    ];

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
