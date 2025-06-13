<?php

namespace Dbronk\Rabbit\Utilities;

class RabbitMqSettings
{
    public function __construct(public readonly string $username, public readonly string $password, public readonly string $hostname, public readonly string $port, public readonly string $queue)
    {
    }
}