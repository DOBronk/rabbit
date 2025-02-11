<?php

require '../vendor/autoload.php';

use Dbronk\Rabbit\Worker;
const settingsfilename = 'settings.ini';

$work = new Worker(settingsfilename);

$work->start();