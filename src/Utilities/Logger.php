<?php

namespace Dbronk\Rabbit\Utilities;
class Logger
{
    const file = "log.txt";

    public static function log($message): string
    {
        $stream = fopen(self::file, "a+");

        if ($stream !== false) {
            try {
                fwrite($stream, date("Y/m/d H:i:s ") . trim($message, " \r\n") . "\n");
            } catch (\Exception $e) {
                echo "Error writing to log file: {$e->getMessage()}\n";
            } finally {
                fclose($stream);
            }
        } else {
            echo "Error opening log file!\n";
        }

        return $message;
    }
}