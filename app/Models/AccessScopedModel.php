<?php

namespace Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Authorization logic based on shared projects of a given user.
 * 
 * If extending this Class, a subquery will be appended allowing only eligible Model(s) to fetched and modified.
 * 
 * Since the shares are based on Projects the implementing Model needs either
 * - a 'project_uuid' column, or
 * - a path to an Entity that has a 'project_uuid' expressed via the $projectRelationship
 */
class AccessScopedModel extends Model
{
    protected $projectRelationship;

    protected static function booted()
    {
        parent::booted(); 
        
        static::addGlobalScope(function(Builder $builder) {
            
            if (!Auth::user()->isAdmin() && !self::projectScopeIsAlreadyApplied($builder)) {
                if(isset((new static)->projectRelationship)){
                    $builder->whereHas((new static)->projectRelationship, function ($query) {
                        $query->whereIn('project_uuid', Auth::user()->shares);
                    });
                } else {
                    $builder->whereIn('project_uuid', Auth::user()->shares);
                }
            }
            
        });
    }

    /**
     * Deduplication of nested $projectRelationship, e.g. plate.shelves on HarvestingPlan.
     */
    protected static function projectScopeIsAlreadyApplied(Builder $builder) {
        return self::checkWhereConditions($builder->getQuery()->wheres);
    }
    private static function checkWhereConditions(array $wheres) {
        foreach ($wheres as $where) {
            if (isset($where['column']) && $where['column'] === 'project_uuid') {
                return true;
            }

            if (isset($where['wheres']) && is_array($where['wheres'])) {
                if (self::checkWhereConditions($where['wheres'])) {
                    return true;
                }
            }

            if ($where['type'] === 'Exists' && isset($where['query']) && is_object($where['query'])) {
                if (self::checkWhereConditions($where['query']->wheres ?? [])) {
                    return true;
                }
            }

            if (isset($where['query']) && is_object($where['query']) && property_exists($where['query'], 'wheres')) {
                if (self::checkWhereConditions($where['query']->wheres)) {
                    return true;
                }
            }
        }

        return false;
    }
}
