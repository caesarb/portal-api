<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Utils\Sanitize;

/**
 * This trait for sure belongs into a utils library rather than in portal-api. 
 * Since it's the only shared library so far, I'll put it here anyway to prevent copying it between our services
 * so I kept the App\Models namespace instead of Portal.
 * 
 * The goal is to support the Searchable vom CRIMSv4 (without the Authorization part) but empowering it with a
 * generic search, e.g. http://thisurl/projects?samples.name=Test%&tm.date<=24-12-2024 will return all Projects
 * which have a sample name starting with 'Test' and which thermofluor run date was at or before Christmas '24.
 * The old search takes precedence, so if a Filter class Samples.Name.php exists, it will take the custom logic
 * defined there. 
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
        $blackListedFilters = ['page','limit','order','sort','password','janitor_key','api_token'];
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
        else {
            if (strpos($value, '%') !== false) {
                $query->where($field, 'LIKE', $value);
            }
            else if (preg_match('/^(<=?|>=?|!=|<>|=)\s*(.+)$/', $value, $matches)) {
                $operator = $matches[1];
                $value = $matches[2];
                $query->where($field, $operator, $value);
            } else {
                $query->where($field, $value);
            }
        }
    }

    /** Generic way. Allows operators and nested fields, e.g.:
     * http://thisurl/projects?samples.name=Test%&tm.date<=21-12-2024 
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
            $nestedParts = explode(':', $field);

            if (count($nestedParts) > 1) {
                $innerRelation = array_shift($nestedParts);
                $innerField = implode(':', $nestedParts);
                self::applyNestedFilter($q, [$innerRelation, $innerField], "$operator $value");
            } else {
                if (strpos($value, '%') !== false) {
                    $q->where($field, 'LIKE', $value);
                } else {
                    $q->where($field, $operator, $value);
                }
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
