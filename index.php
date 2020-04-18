<?php

use RCore\Handlers\Paths;
use RCore\Handlers\Routes;
use RCore\Main;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

(new Main(
    new Paths('.env', 'templates'), new Routes())
)->serve();