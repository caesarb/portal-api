<?php

namespace Portal\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $fillable = [
        'id', 'firstname', 'familyname', 'username', 'uuid', 'email', 'telephone', 'orcid', 'email_verified_at', 'role_id', 'group_id', 'shares'
    ];

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function group() {
        return $this->belongsTo(Group::class);
    }

    public function shares() {
        return $this->hasMany(Share::class);
    }


    public function isAdmin(){
        return $this->role_id < 3 ? true : false;
    }

    //========= ChiefUser Interface ==============
    public function getRole() {
        return $this->role->name;
    }
    public function isOwner($user_uuid)
	{
		return $this->uuid === $user_uuid;
	}
	
	public function isSuperAdmin()
	{
		return $this->role_id === 1;
	}
	
	public function isGroupLeader()
	{
		return $this->role_id === 3;
	}

    public function isGroupLeaderFor($user_uuid)
    {
        if (!$this->isGroupLeader()) {
            return false;
        }
    
        $portal = new PortalClient();
        $u = $portal->getUser($user_uuid);
        
        return $u->uuid === $this->uuid;
    }

}
