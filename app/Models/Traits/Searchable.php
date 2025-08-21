<?php

namespace Common\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Utils\Sanitize;

/**
 * This trait for sure belongs into a utils library rather than in portal-api. 
 * Since it's the only shared library so far, I'll put it here anyway to prevent copying it between our services
 * but put the Common namespace instead of Portal.
 * 
 * The goal is to support the Searchable vom CRIMSv4 (without the Authorization part) but empowering it with a
 * generic search. A GET http://thisurl/projects?samples:name=Test%&tm:date<=24-12-2024 will return all Projects
 * which have a sample name starting with 'Test' and which thermofluor run date was at or before Christmas '24.
 * The 'old' search takes precedence, so if a specific Filter class exists, it'll be executed. 
 */
trait Searchable
{
    protected $sanitize;

    public static function filter(Builder $query)
    {
        $filters = request()->all();

        // filter by fields
        $query = static::ApplyFiltersToQuery($query, $filters);
        $query = static::OrderQueryBy($query, $filters);
        $limit = static::findLimit($filters);

        return $query->paginate($limit)->onEachSide(1);
    }

    private static function ApplyFiltersToQuery(Builder $query, $filters)
    {
        $blackListedFilters = ['page','limit','order','sort','password','janitor_key','api_token','withTrashed'];
        foreach ($filters as $name => $value) {
            if(!in_array($name, $blackListedFilters)){
                if (strpos($name, ':') !== false) {
                    // Split the filter name to handle nested relations
                    $parts = explode(':', $name);
                    self::applyNestedFilter($query, $parts, $value);
                } else {
                    // Handle the filter as a simple direct filter
                    self::applyDirectFilter($query, $name, $value);
                }
            }
        }

        // disable SoftDeletes if 'withTrashed=true' is requested
        if (isset($filters['withTrashed']) && $filters['withTrashed'] == 'true' && 
            in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($query->getModel()))) {
            $query->withTrashed();
        }

        return $query;
    }

    private static function applyDirectFilter(Builder $query, $field, $value)
    {
        // Old way: Determine the filter class and apply it
        $filter = '\App\Models\Filters\\' . Str::studly($field);
        if (class_exists($filter)) {
            $query = $filter::apply($query, $value);
        } 
        // New way to parse operator and like queries from the FE
        else if (Schema::hasColumn($query->getModel()->getTable(), $field)){
            if (strpos($value, '%') !== false) {
                $query->whereRaw("$field ILIKE ?", [$value]); // case-insensitive
            }
            else if (preg_match('/^(<=?|>=?|!=|<>|=)\s*(.+)$/', $value, $matches)) {
                $operator = $matches[1];
                $value = $matches[2];
                $query->where($field, $operator, $value);
            } else {
                $query->where($field, $value);
            }
        }
        else {
            \Log::warning("Searchable: Filter '$field' does not exist in model " . get_class($query->getModel()) . 
                " or is not a valid column. Skipping this filter.");
        }
    }

    /** Generic way. Allows operators and nested fields, e.g.:
     * http://thisurl/projects?samples:name=Test%&tm.date<=21-12-2024 
     */
    private static function applyNestedFilter(Builder $query, array $parts, $value)
    {
        $relation = array_shift($parts);
        $field = implode(':', $parts);

        if (preg_match('/^(<=?|>=?|!=|<>|=)\s*(.+)$/', $value, $matches)) {
            $operator = $matches[1];
            $value = $matches[2];
        } else {
            $operator = '=';
        }

        $query->whereHas($relation, function (Builder $q) use ($field, $operator, $value) {
            
            // if trashed records are requested, we will also apply it to relations if these models use SoftDeletes 
            if (request()->has('withTrashed') && request()->get('withTrashed') == 'true' &&
                in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($q->getModel()))) {
                $q->withTrashed();
            }
            
            $nestedParts = explode(':', $field);

            if (count($nestedParts) > 1) {
                $innerRelation = array_shift($nestedParts);
                $innerField = implode(':', $nestedParts);
                self::applyNestedFilter($q, [$innerRelation, $innerField], "$operator $value");
            } else if (Schema::hasColumn($q->getModel()->getTable(), $field)) {
                if (strpos($value, '%') !== false) {
                    $q->whereRaw("$field ILIKE ?", [$value]);
                } else {
                    $q->where($field, $operator, $value);
                }
            } else {
                \Log::warning("Searchable: Filter '$field' does not exist in model " . get_class($q->getModel()) . 
                    " or is not a valid column. Skipping this filter.");
            }
        });
    }


    private static function OrderQueryBy(Builder $query, $filters)
    {
        $sanitize = new Sanitize();
        $sort = Arr::has($filters, 'sort')
            ? trim($sanitize->string($filters['sort']))
            : 'created_at';

        $order = Arr::has($filters, 'order')
            ? trim($sanitize->string(strtoupper($filters['order'])))
            : 'DESC';


        return $query->orderBy($sort, $order);
    }

    private static function findLimit($filters)
    {
        $sanitize = new Sanitize();
        return Arr::has($filters, 'limit')
            ? trim($sanitize->integer($filters['limit']))
            : 15;
    }
}
