<?php

namespace Dbronk\Rabbit\Utilities;

class OllamaSettings
{
    public function __construct(public readonly string $host, public readonly string $port)
    {

    }
}