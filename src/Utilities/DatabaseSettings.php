<?php

namespace Dbronk\Rabbit\Utilities;

class DatabaseSettings
{
    public function __construct(public readonly string $host, public readonly string $port, public readonly string $user, public readonly string $password, public readonly string $database)
    {

    }
}