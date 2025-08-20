<?php

namespace Dbronk\Rabbit\Services;

class OllamaService
{
    public function __construct(private readonly string $host, private readonly int $port) {}

    public function findIssues(string $code)
    {
        $response = json_decode(Http::post("http://$this->host:$this->port/api/generate", [
            'model' => 'devstral:latest', // 'qwen2.5-coder:14b',
            'prompt' => 'Review this code for SOLID and DRY practises. Format your response in JSON and use'.
                ' SRP, OCP, LSP, ISP, DIP and DRY as attributes. If there are problems set the attribute to a string with improvements, '.
                "else if no problems or if not applicable to the code set the attribute to integer value 1.\n\n{$code}",

            'format' => 'json',
            'stream' => false,
        ])->getContent(), true);

        if (array_key_exists('response', $response)) {
            return $response['response'];
        }
    }
}
