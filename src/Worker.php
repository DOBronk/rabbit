<?php

namespace Dbronk\Rabbit;

use Dbronk\Rabbit\DTO\JobDTO;
use Dbronk\Rabbit\Services\DbService;
use Dbronk\Rabbit\Services\OllamaService;
use Dbronk\Rabbit\Utilities\Logger;
use Dbronk\Rabbit\Utilities\Settings;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;

class Worker
{
    private int $failures = 0;

    public function __construct(private readonly Settings $settings, private readonly int $fail_max)
    {
    }

    public function start(): void
    {
        $ollama = new OllamaService($this->settings->ollama()->host, $this->settings->ollama()->port);
        try {
            $connection = new AMQPStreamConnection(
                $this->settings->rabbitMq()->hostname,
                $this->settings->rabbitMq()->port,
                $this->settings->rabbitMq()->username,
                $this->settings->rabbitMq()->password,
            );

            $channel = $connection->channel();
            // Persistent queue channel
            $channel->queue_declare($this->settings->rabbitMq()->queue, false, true, false, false);
        } catch (Exception $exception) {
            echo Logger::log("Fatal error! Exception: " . $exception->getMessage());
            exit();
        }

        echo Logger::log(" [*] Worker is waiting for jobs. To exit press CTRL+C\n");

        $callback = function ($msg) use ($ollama) {
            try {
                $task = JobDTO::fromJson($msg->body);
            } catch (Exception $e) {
                echo Logger::log(" [x] Received invalid message, deleting from queue. Exception message: {$e->getMessage()}\n");
                Logger::log('Message body: ' . $msg->body);
                $msg->ack();
                return;
            }

            $status = 1;
            echo Logger::log(" [x] Received job, item id:  $task->jobItemId\n");

            try {
                $response = $ollama->findIssues($task->payload);
            } catch (Exception $e) {
                $status = 2;
                echo Logger::log(" [x] Job failed on ollama API, assigning status code 2. Exception message: {$e->getMessage()}\n");
            }

            try {
                $echo = ' [x] Job completed successfully';
                if (!DbService::getInstance()->updateItem($task->jobItemId, $status, $task->id, $response ?? '')) {
                    $echo .= ', although no row was affected in database! Removing from pool (non existent id)';
                    $this->failures++;
                } else {
                    $this->failures = 0;
                }
                $msg->ack();
                echo Logger::log($echo), "\n";
            } catch (Exception $e) {
                echo Logger::log(" [x] Failed to commit to database. Job will be returned to pool. Exception message: {$e->getMessage()}\n");
                $this->failures++;
            }

            if ($this->fail_max > 0 && $this->failures >= $this->fail_max) {
                echo Logger::log('Fatal error: Too many consecutive database errors, shutting worker down!');
                exit();
            }
        };

        // Acknowledgements enabled
        $channel->basic_consume($this->settings->rabbitMq()->queue, '', false, false, false, false, $callback);

        try {
            $channel->consume();
        } catch (Throwable $exception) {
            echo Logger::log("Fatal error: {$exception->getMessage()}");
        }
    }

}