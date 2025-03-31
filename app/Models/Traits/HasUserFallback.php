<?php
namespace Portal\Models\Traits;

use Portal\Models\User;
use Portal\Models\UserAud;

trait HasUserFallback
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid')
            ->withDefault(function ($user, $parent) {
                return UserAud::where('uuid', $parent->getAttribute('user_uuid'))
                    ->orderBy('revision_created', 'desc')
                    ->first();
            });
    }
}
