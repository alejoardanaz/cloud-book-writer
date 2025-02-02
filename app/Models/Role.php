<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const COLLABORATOR_STRING = 'collaborator';

    protected $fillable = [
        'name'
    ];

    public static function getCollaboratorRoleId()
    {
        return self::where('name', self::COLLABORATOR_STRING)->first()->id;
    }
}
