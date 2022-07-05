<?php

namespace Liuggio\Fastest\Doctrine\DBAL\Factory;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;

class SqlConnectionFactory extends ConnectionFactory
{
    private const DSN_TEMPLATE = '{{PROTOCOL}}://{{USER}}:{{PASSWORD}}@{{HOST}}:{{PORT}}/{{DB}}?{{PARAMS}}';

    private const DEFAULT_PORTS = [
        'mysql'      => 3306,
        'postgresql' => 5432,
    ];

    private const PARAMS_LIST = [
        'default' => [
            'driver',
            'host',
            'port',
            'user',
            'password',
            'charset',
        ],
        'dsn' => ['url', 'dbname'],
    ];

    /**
     * @throws Exception
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ): Connection {

        if ($this->isMasterSlaveOrUnique($params)) {
            $originalDatabaseName = $params['master']['dbname'] ?? $params['dbname'] ?? null;
            $params['dbname']     = $originalDatabaseName ? $this->getDbNameFromEnv($originalDatabaseName) : null;
            $params               = $this->prepareParamsByConnectionType($params);

            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        if ($this->isDsn($params)) {
            [$dsn, $databaseName] = $this->getCompiledDsn($params);

            $params['url']    = $dsn;
            $params['dbname'] = $this->getDbNameFromEnv($databaseName);
            $params           = $this->prepareParamsByConnectionType($params);

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

    private function getCompiledDsn(array $params = []): array
    {
        [$protocol, $user, $password, $databaseName, $host, $port, $parameters] = $this->getInfoFromDsn($params);

        $dsn = str_replace(
            ['{{PROTOCOL}}', '{{USER}}', '{{PASSWORD}}', '{{HOST}}', ':{{PORT}}', '{{DB}}', '?{{PARAMS}}'],
            [
                $protocol,
                $user,
                $password,
                $host,
                $port ? ":$port" : ':'.self::DEFAULT_PORTS[$protocol],
                $databaseName,
                $parameters ? "?$parameters" : '',
            ],
            self::DSN_TEMPLATE
        );

        return [$dsn, $databaseName];
    }

    private function getInfoFromDsn(array $params): array
    {
        $dsn = preg_match(self::CONNECTION_STRING_PATTERNS['sql'], $params['url'] ?? '', $dsnPieces);

        $isValidDsn = $dsn
            && !empty($dsnPieces['protocol'])
            && !empty($dsnPieces['user'])
            && !empty($dsnPieces['password'])
            && !empty($dsnPieces['database'])
            && !empty($dsnPieces['host']);

        if ($isValidDsn) {
            return [
                $dsnPieces['protocol'],
                $dsnPieces['user'],
                $dsnPieces['password'],
                $dsnPieces['database'],
                $dsnPieces['host'],
                $dsnPieces['port'] ?? null,
                $dsnPieces['parameters'] ?? null,
            ];
        }

        throw new InvalidArgumentException(sprintf('Unable to get database name from DSN <%s>', $params['url'] ?? ''));
    }

    private function prepareParamsByConnectionType(array $params, string $connectionType = 'default'): array
    {
        $paramsList = self::PARAMS_LIST[$connectionType];

        return array_diff_key($params, array_flip($paramsList));
    }
}
