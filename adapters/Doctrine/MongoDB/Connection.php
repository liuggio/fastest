<?php

namespace Liuggio\Fastest\Doctrine\MongoDB;

use MongoDB\Client as BaseConnection;
use Liuggio\Fastest\Process\EnvCommandCreator;

class Connection extends BaseConnection
{
    public function selectDatabase($databaseName, array $options = [])
    {
        $databaseName = $this->getDbNameFromEnv($databaseName);

        return parent::selectDatabase($databaseName, $options);
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
