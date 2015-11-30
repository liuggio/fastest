<?php

namespace Liuggio\Fastest\Doctrine\DBAL;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Liuggio\Fastest\Process\EnvCommandCreator;

/**
 * Creates a connection taking the db name from the env, this is great if you want to run parallel functional tests.
 */
class ConnectionFactory extends BaseConnectionFactory
{
    /**
     * Create a connection by name.
     *
     * @param array         $params
     * @param Configuration $config
     * @param EventManager  $eventManager
     * @param array         $mappingTypes
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
    {
        $dbName = $this->getDbNameFromEnv($params['dbname']);
                
        if ($params['driver'] === 'pdo_sqlite') {
            $params['path'] = str_replace("__DBNAME__", $dbName, $params['path']);
        } else {
            $params['dbname'] = $dbName;
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
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

        return (!empty($dbName));
    }

    private function getDbNameEnvValue()
    {
        return getenv(EnvCommandCreator::ENV_TEST_CHANNEL_READABLE);
    }
}
