<?php
namespace Portal\Models\Traits;

use Portal\Models\User;
use Portal\Models\UserAud;
use Portal\Models\BelongsToUserWithAudit;

trait HasUserFallback
{
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_uuid', 'uuid')
    //         ->withDefault(function ($user, $parent) {
    //             return UserAud::where('uuid', $parent->getAttribute('user_uuid'))
    //                 ->orderBy('revision_created', 'desc')
    //                 ->first();
    //         });
    // }

    public function user()
    {
        return $this->belongsToUserWithAudit(
            User::class,
            UserAud::class,
            'user_uuid',
            'uuid'
        );
    }

    protected function belongsToUserWithAudit(
        string $related,
        string $auditRelated,
        ?string $foreignKey = null,
        ?string $ownerKey = null,
        ?string $relation = null,
    ) {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $instance->getForeignKey();
        $ownerKey = $ownerKey ?: $instance->getKeyName();
        $relation = $relation ?: $this->guessBelongsToRelation();

        return new BelongsToUserWithAudit(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation,
            $auditRelated
        );
    }
}
