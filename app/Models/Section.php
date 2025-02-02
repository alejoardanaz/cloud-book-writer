<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'parent_id',
        'title',
        'content'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function parent()
    {
        return $this->belongsTo(Section::class);
    }

    public function children()
    {
        return $this->hasMany(Section::class, 'parent_id');
    }
}
