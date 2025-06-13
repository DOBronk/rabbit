<?php

namespace Dbronk\Rabbit\Services;

use Symfony\Component\HttpClient\HttpClient;

class Http
{
    public static function post(string $url, array $vars = [])
    {
        $client = HttpClient::create();

        return $client->request(
            'POST',
            $url,
            [
                'timeout' => 900,   // 15 minuten
                'json' => $vars
            ]
        );
    }
}
