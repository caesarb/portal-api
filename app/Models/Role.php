<?php

namespace Portal\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'id', 'name'
    ];
}