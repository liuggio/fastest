#!/usr/bin/env php
<?php

define('SCRIPT_NAME', __FILE__);
$loader = require __DIR__ . "/vendor/autoload.php";

use Liuggio\Fastest\Application;

$application = new Application();
$application->run();