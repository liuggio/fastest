<?php

namespace Liuggio\Fastest;

use Symfony\Component\Console\Application as BaseApp;
use Liuggio\Fastest\Queue;
use Liuggio\Fastest\Command;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Application extends BaseApp
{
    const VERSION = '1.0-dev';

    private $redisQueue;
    private $services;

    public function __construct(AppParametersInterface $parameters)
    {
        parent::__construct('fastest', self::VERSION);

        $this->redisQueue = new Queue\Infrastructure\PredisQueue(
            $parameters->getRedisQueueName(),
            $parameters->getRedisHost(),
            $parameters->getRedisPort()
        );

        $log = new Logger('default');
        $log->pushHandler(new StreamHandler($parameters->getLogPath(), $parameters->getLogLevel()));
        $this->services['log'] =  $log;

        $this->services['pop'] = new Queue\PopATestSuite($this->redisQueue);
        $this->services['push'] = new Queue\PushTestSuites($this->redisQueue);
        $this->services['pipe'] = new Queue\CreateTestSuitesFromPipe();
        $this->services['xml'] = new Queue\CreateTestSuitesFromPhpUnitXML();
        $this->services['parallel_command'] = new Queue\PrepareParallelCommand();

        $this->add(new Command\ConsumerCommand($this->services['pop']));
        $this->add(new Command\ParallelCommand());
    }

    public function getService($name)
    {
        return $this->services[$name];
    }
} 