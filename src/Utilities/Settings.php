<?php

namespace Dbronk\Rabbit\Utilities;

class Settings
{
    private DatabaseSettings $database;
    private RabbitMqSettings $rabbitMq;
    private OllamaSettings $ollama;

    public function __construct()
    {

    }

    public function parse_ini(string $filename)
    {
        $settings = parse_ini_file($filename, true);

        try {
            $this->database = new DatabaseSettings(
                $settings['database']['hostname'],
                $settings['database']['password'],
                $settings['database']['username'],
                $settings['database']['password'],
                $settings['database']['db']);
            $this->rabbitMq = new RabbitMqSettings(
                $settings['rabbitmq']['username'],
                $settings['rabbitmq']['password'],
                $settings['rabbitmq']['hostname'],
                $settings['rabbitmq']['port'],
                $settings['rabbitmq']['queue']
            );
            $this->ollama = new OllamaSettings(
                $settings['ollama']['hostname'],
                $settings['ollama']['port'],
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function database(): DatabaseSettings
    {
        return $this->database;
    }

    public function rabbitMq(): RabbitMqSettings
    {
        return $this->rabbitMq;
    }

    public function ollama(): OllamaSettings
    {
        return $this->ollama;
    }

    function array_keys_exists(array $keys, array $arr)
    {
        return !array_diff_key(array_flip($keys), $arr);
    }
}