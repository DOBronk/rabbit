<?php

require '../vendor/autoload.php';

use Dbronk\Rabbit\Services\DbService;
use Dbronk\Rabbit\Utilities\Settings;
use Dbronk\Rabbit\Worker;
use Dbronk\Rabbit\Utilities\Logger;

// Settings filename
const settingsfilename = 'settings.ini';

$settings = new Settings();
// Maximum number of consecutive database errors before shutting worker down, 0 = unlimited/disabled
const max_fail = 3;

try {
    $settings->parse_ini(settingsfilename);
} catch (Exception $e) {
    echo Logger::log("Fatal error: Failed to parse settings file! Exception: {$e->getMessage()}\n");
    exit();
}

DbService::setSettings($settings->database());

$work = new Worker($settings, max_fail);
$work->start();