<?php

use RCore\Handlers\Paths;
use RCore\Handlers\Routes\RoutesBase;
use RCore\Main;

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT', dirname(__FILE__, 2) . '/');

require ROOT . 'vendor/autoload.php';

(new Main(
    new Paths(ROOT . '.env', ROOT . 'templates'), new RoutesBase())
)->serve();