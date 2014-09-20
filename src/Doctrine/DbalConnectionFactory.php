<?php

namespace Liuggio\Fastest\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Liuggio\Fastest\Process\EnvCommandCreator;

/**
 * Creates a connection taking the db name from the env, this is great if you want to run parallel functional tests.
 */
class DbalConnectionFactory extends ConnectionFactory
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
        $params['dbname'] = $this->modifyDbNameFromEnv($params['dbname']);

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

    private function modifyDbNameFromEnv($dbName)
    {
        if ((!isset($dbName) || (strpos($dbName, "test") === 0)) && $this->issetDbNameEnvValue()) {
            return $this->getDbNameEnvValue();
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
        return getenv(EnvCommandCreator::ENV_TEST_DB_NAME);
    }
}
