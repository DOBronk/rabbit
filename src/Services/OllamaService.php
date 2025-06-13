<?php

namespace Dbronk\Rabbit\Services;

class OllamaService
{
    public function __construct(private readonly string $host, private readonly int $port)
    {
    }

    public function findIssues(string $code)
    {
        $response = json_decode(Http::post("http://$this->host:$this->port/api/generate", [
            'model' => 'qwen2.5-coder:14b',
            'prompt' => "can you tell me if this code uses proper SOLID and DRY principles and return the output as json using the following keys: SRP for Single Responsibility Principle, OCP for Open/Closed Principle, LSP for Liskov Substitution Principle, ISP for Interface Segregation Principle, DIP for Dependency Inversion Principle and DRY for DRY Principle." .
                ": {$code}",
            'format' => 'json',
            'stream' => false,
        ])->getContent(), true);

        if (array_key_exists('response', $response)) {
            return $response['response'];
        }
    }
}