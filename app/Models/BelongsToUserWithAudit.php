<?php

namespace Portal\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;

class BelongsToUserWithAudit extends BelongsTo
{
    protected string $auditModel;

    public function __construct(
        $query,
        $child,
        $foreignKey,
        $ownerKey,
        $relationName,
        string $auditModel
    ) {
        parent::__construct($query, $child, $foreignKey, $ownerKey, $relationName);

        $this->auditModel = $auditModel;
    }

    /**
     * Lazy loading
     */
    public function getResults()
    {
        $result = parent::getResults();

        if ($result) {
            return $result;
        }

        $key = $this->child->{$this->foreignKey};

        if (!$key) {
            return null;
        }
        $result = $this->auditModel::where('uuid', $key)
            ->latest('revision_created')
            ->first();

        $this->child->setRelation($this->relationName, $result);

        return $result;
    }

    /**
     * Eager loading
     */
    public function match(array $models, Collection $results, $relation)
    {
        parent::match($models, $results, $relation);

        $missing = collect($models)
            ->filter(fn ($model) => !$model->relationLoaded($relation) || $model->getRelation($relation) === null);

        if ($missing->isEmpty()) {
            return $models;
        }

        $uuids = $missing
            ->pluck($this->foreignKey)
            ->filter()
            ->unique();

        $audits = $this->auditModel::query()
            ->whereIn('uuid', $uuids)
            ->orderByDesc('revision_created')
            ->get()
            ->unique('uuid')
            ->keyBy('uuid');

        foreach ($missing as $model) {
            $uuid = $model->{$this->foreignKey};

            if ($audits->has($uuid)) {
                $model->setRelation($relation, $audits[$uuid]);
            }
        }

        return $models;
    }
}