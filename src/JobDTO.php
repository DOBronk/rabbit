<?php

namespace Dbronk\Rabbit;

class JobDTO
{
    private function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $jobItemId,
        public readonly string $payload
    ) {
    }

    public static function fromArray(array $array): JobDTO
    {
        return new self($array['id'], $array['userId'], $array['jobItemId'], $array['payload']);
    }

    public static function fromJson(string $json): JobDTO
    {
        $array = json_decode($json, true);
        return self::fromArray($array);
    }
    public function toJson(): string
    {
        return json_encode($this);
    }
}
