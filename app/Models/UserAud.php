<?php

namespace Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Common\Models\Traits\IsReadOnly;

/**
 * Versioning Table of Users, will be queried in rare cases where we e.g. want to display the username of a deleted user.
 */
class UserAud extends Model
{
    use IsReadOnly;
    
    protected $connection = 'portal';
    protected $table = 'users_aud';
}
