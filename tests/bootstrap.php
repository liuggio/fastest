<?php

/*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('Liuggio\\Fastest\\', __DIR__.'/Fastest');
define('SCRIPT_NAME', 'fastest');