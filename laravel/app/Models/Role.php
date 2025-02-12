<?php

namespace Portal\Models;

class Role
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}