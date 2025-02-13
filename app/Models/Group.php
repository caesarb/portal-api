<?php

namespace Portal\Models;

class Group
{
    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}