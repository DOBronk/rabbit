<?php

namespace Dbronk\Rabbit;

use Dbronk\Rabbit\OllamaService;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Dbronk\Rabbit\JobDTO;
use Dbronk\Rabbit\DbService;
use Throwable;

class Worker
{
    private array $settings;

    public function __construct(string $settingsfile)
    {
        $this->settings = parse_ini_file($settingsfile, true);
    }

    public function start()
    {
        $ollama = new OllamaService($this->settings['ollama']['hostname'], $this->settings['ollama']['port']);

        $connection = new AMQPStreamConnection(
            $this->settings['rabbitmq']['hostname'],
            $this->settings['rabbitmq']['port'],
            $this->settings['rabbitmq']['username'],
            $this->settings['rabbitmq']['password'],
        );

        $channel = $connection->channel();

        // Persistent queue channel
        $channel->queue_declare($this->settings['rabbitmq']['queue'], false, true, false, false);
        
        echo " [*] Worker is waiting for jobs. To exit press CTRL+C\n";

        $callback = function ($msg) use ($ollama) {
            try {
                $task = JobDTO::fromJson($msg->body);
            } catch (Exception $e) {
                echo ' [x] Received invalid message, deleting from queue. Exception message: ' . $e->getMessage() . "\n";
                $msg->ack();
                return;
            }

            $response = '';
            $status = 1;

            echo ' [x] Received job, item id:  ', $task->jobItemId, "\n";

            try {
                $response = $ollama->findIssues($task->payload);
            } catch (Exception $e) {
                $status = 2;
                echo ' [x] Job failed on ollama API, assigning status code 2. Exception message: ', $e->getMessage(), "\n";
            }

            try {
                DbService::getInstance()->updateItem($task->jobItemId, $status, $task->id, $response);
                $msg->ack();
                echo ' [x] Job completed successfully', "\n";
            } catch (Exception $e) {
                echo ' [x] Failed to commit to database. Job will be returned to pool. Exception message: ', $e->getMessage(), "\n";
            }
        };

        // Acknowledgements enabled
        $channel->basic_consume($this->settings['rabbitmq']['queue'], '', false, false, false, false, $callback);

        try {
            $channel->consume();
        } catch (Throwable $exception) {
            echo $exception->getMessage();
        }
    }

}