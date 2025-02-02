<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title', 
        'description'
    ];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'book_users')
            ->withPivot('role_id')
            ->wherePivot('role_id', Role::getCollaboratorRoleId())
            ->withTimestamps();
    }
}
