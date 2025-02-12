<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'id', 'firstname', 'familyname', 'username', 'uuid', 'email', 'telephone', 'orcid', 'email_verified_at', 'role_id', 'group_id', 'shares', 'role'
    ];

    protected $hidden = [
        'remember_token'
    ];

    public Role $role;
    public function setRoleAttribute($value)
    {
        $this->role = new Role($value['name']);
    }

    public function isAdmin(){
        return $this->role_id < 3 ? true : false;
    }
}
