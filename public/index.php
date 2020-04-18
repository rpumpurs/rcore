<?php

use RCore\Handlers\Paths;
use RCore\Handlers\Routes;
use RCore\Main;

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT', dirname(dirname(__FILE__)) . '/');

require ROOT . 'vendor/autoload.php';

(new Main(
    new Paths(ROOT . '.env', ROOT . 'templates'), new Routes())
)->serve();