<?php

namespace Common\Models\Traits;

/** 
 * Since I can't rewrite all microservices to use API instead of directly accessing other ms' DB,
 * here's a try to shield exposed entities for misuse (modify, delete)
 */
trait IsReadOnly 
{
    public function save(array $options = [])
    {
        throw new \Exception("Trying to save a read-only Model.");
    }

    public function delete()
    {
        throw new \Exception("Trying to delete a read-only Model.");
    }

    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception("Trying to update a read-only Model.");
    }

    public static function create(array $attributes = [])
    {
        throw new \Exception("Trying to create a read-only Model.");
    }

    public function forceDelete()
    {
        throw new \Exception("Trying to forceDelete a read-only Model.");
    }
}