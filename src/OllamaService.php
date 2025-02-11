<?php

namespace Dbronk\Rabbit;

use Dbronk\Rabbit\Http;

class OllamaService
{
    public function __construct(private readonly string $host, private readonly int $port)
    {
    }

    public function findIssues(string $code)
    {
        $response = json_decode(Http::post("http://$this->host:$this->port/api/generate", [
            'model' => 'qwen2.5-coder:14b',
            'prompt' => "can you tell me if this code uses proper SOLID and DRY principles and return the output as json " .
                ": {$code}",
            'format' => 'json',
            'stream' => false,
        ])->getContent(), true);

        if (array_key_exists('response', $response)) {
            return $response['response'];
        }
    }
}