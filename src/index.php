<?php

use app\core\Main;


error_reporting(~E_NOTICE);
define('ROOT', '');
define('OPTION', ['base_uri' => ROOT, 'verify' => false, 'timeout' => 10]);
define('PROCESS_LIMIT', 50);

require_once 'vendor/autoload.php';

$parser = new Main();
$parser->start();



