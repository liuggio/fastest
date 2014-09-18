<?php

namespace Liuggio\Fastest\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

/**
 * Connection
 */
class DbalConnectionFactory extends ConnectionFactory
{
    public static $envVarName = 'TEST_ENV_NUMBER';
    public static $envIsAvailable = 'TEST_ENV_ENABLE';
    public static $envMaxNumber = 'TEST_ENV_MAX_NUMBER';
    public static $defaultMaxNumber = 4;

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
        $params['dbname'] = self::modifyDbNameFromEnv($params['dbname']);

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

    private static function getMaxNumber()
    {
        $max = (int) getenv(self::$envMaxNumber);

        if (0 == $max) {
            return self::$defaultMaxNumber;
        }

        return $max;
    }

    private static function getEnvValue()
    {
        $envNumber = (int) getenv(self::$envVarName);
        if ($envNumber == false || empty($envNumber) || ($envNumber <= 0)) {
            return '1';
        }
        $max = self::getMaxNumber();
        $envNumber = $envNumber % $max;

        if ($envNumber == 0) {
            return (string) $max;
        }

        return $envNumber;
    }

    private static function isParallelizeEnable()
    {
        $boolean = (int) getenv(self::$envIsAvailable);

        return (bool) $boolean;
    }

    private static function modifyDbNameFromEnv($dbName)
    {
        if (!self::isParallelizeEnable()) {
            return $dbName;
        }

        if (!isset($dbName) || (strpos($dbName, "test") === 0)) {
            return 'test_'.self::getEnvValue();
        }

        return $dbName;
    }
}
