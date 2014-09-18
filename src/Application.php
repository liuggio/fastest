<?php

namespace Liuggio\Fastest;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application as BaseApp;

class Application extends BaseApp
{
    const VERSION = '1.0-dev';

    public function __construct()
    {
        parent::__construct('fastest', self::VERSION);

        $this->add(new Command\ConsumerCommand());
        $this->add(new Command\ParallelCommand());
    }
}
