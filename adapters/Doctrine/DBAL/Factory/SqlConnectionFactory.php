<?php

namespace Liuggio\Fastest\Doctrine\DBAL\Factory;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;

class SqlConnectionFactory extends ConnectionFactory
{
    private const DSN_REPLACE = '$protocol://$user:$password@$host:__DBPORT__/$database__DBNAME__?$parameters';

    private const DEFAULT_PORTS = [
        'mysql' => 3306,
        'postgresql' => 5432
    ];

    private const PARAMS_LIST = [
        'default' => [
            'driver',
            'host',
            'port',
            'dbname',
            'user',
            'password',
            'charset',
        ],
        'url' => ['url']
    ];

    /**
     * @throws Exception
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ): Connection
    {
        if ($this->isDsn($params)) {
            $params['url'] = $this->getCompiledDsn($params);
            $params = $this->prepareParamsByConnectionType($params, 'url');
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        if($this->isMasterSlaveOrUnique($params)) {
            $originalDatabaseName = $params['master']['dbname'] ?? $params['dbname'] ?? null;
            $params['dbname'] = $originalDatabaseName ? $this->getDbNameFromEnv($originalDatabaseName) : null;
            $params = $this->prepareParamsByConnectionType($params);
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

    private function isDsn(array $params): bool
    {
        return !empty($params['url']) && preg_match_all(self::CONNECTION_STRING_PATTERNS['sql'], $params['url']);
    }

    private function isMasterSlaveOrUnique(array $params): bool
    {
        return isset($params['master']['dbname']) || isset($params['dbname']);
    }

    private function getCompiledDsn(array $params = []): string
    {
        $compiledDsn = preg_replace(
            self::CONNECTION_STRING_PATTERNS['sql'],
            self::DSN_REPLACE,
            $params['url']
        );

        if(!is_string($compiledDsn)) {
            throw new InvalidArgumentException(
                sprintf('The provided DSN isn`t valid <%s>', $params['url'])
            );
        }

        [$protocol, $databaseName, $port] = $this->getInfoFromDsn($params);

        return str_replace(
            [self::DATABASE_NAME_PLACEHOLDER, self::DATABASE_PORT_PLACEHOLDER],
            [$this->getDbNameFromEnv($databaseName), $port ?: self::DEFAULT_PORTS[$protocol]],
            $compiledDsn
        );
    }

    private function getInfoFromDsn(array $params): array
    {
        $dsn = preg_match_all(self::CONNECTION_STRING_PATTERNS['sql'], $params['url'] ?? '', $dsnPieces);

        if($dsn && !empty($dsnPieces['database']) && !empty($dsnPieces['protocol'])) {
            return [$dsnPieces['protocol'], $dsnPieces['database'], $dsnPieces['port'] ?? null];
        }

        throw new InvalidArgumentException(
            sprintf('Unable to get database name from DSN <%s>', $params['url'] ?? '')
        );
    }

    private function prepareParamsByConnectionType(array $params, string $connectionType = 'default'): array
    {
        $paramsList = $connectionType === 'default' ? self::PARAMS_LIST['url'] : self::PARAMS_LIST[$connectionType];

        return array_diff_key($params,array_flip($paramsList));
    }
}