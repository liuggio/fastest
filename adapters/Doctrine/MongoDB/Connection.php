<?php

namespace Liuggio\Fastest\Doctrine\MongoDB;

use Doctrine\MongoDB\Connection as BaseConnection;
use Liuggio\Fastest\Process\EnvCommandCreator;

class Connection extends BaseConnection
{
    public function selectDatabase($name)
    {
        $name = $this->getDbNameFromEnv($name);

        return parent::selectDatabase($name);
    }

    private function getDbNameFromEnv($dbName)
    {
        if ($this->issetDbNameEnvValue()) {
            return $dbName.'_'.$this->getDbNameEnvValue();
        }

        return $dbName;
    }

    private function issetDbNameEnvValue()
    {
        $dbName = $this->getDbNameEnvValue();

        return !empty($dbName);
    }

    private function getDbNameEnvValue()
    {
        return getenv(EnvCommandCreator::ENV_TEST_CHANNEL_READABLE);
    }
}
