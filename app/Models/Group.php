<?php

namespace Portal\Models;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'id', 'name', 'organisation_id'
    ];

}