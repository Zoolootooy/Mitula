<?php

use app\core\Main;


error_reporting(~E_NOTICE);
define('ROOT', '');
define('OPTION', ['base_uri' => ROOT, 'verify' => false, 'timeout' => 10, 'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0']]);
define('PROCESS_LIMIT', 50);

require_once 'vendor/autoload.php';

$parser = new Main();
$parser->start();



