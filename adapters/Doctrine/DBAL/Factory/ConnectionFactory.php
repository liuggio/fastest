<?php

namespace Liuggio\Fastest\Doctrine\DBAL\Factory;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as BaseConnectionFactory;
use Liuggio\Fastest\Process\EnvCommandCreator;

class ConnectionFactory extends BaseConnectionFactory
{
    protected const CONNECTION_STRING_PATTERNS = [
        'sql' => '/(?P<protocol>^(mysql|postgresql))\:\/\/(?P<user>.+)?\:(?P<password>.+)'.
            '?\@(?P<host>.+)\:(?P<port>\d{4})\/(?P<database>.+)\?(?P<parameters>[a-zA-Z].+=.+&?)/',
        'sqlite' => '/(?P<protocol>^(sqlite))\:\/\/(\%kernel\..+\%|.+)(?P<database>.*\.db$)/',
    ];

    protected function getDbNameFromEnv(string $dbName): string
    {
        if ($this->issetDbNameEnvValue()) {
            return preg_match('/\d$/m', $dbName)
                ? $dbName.'_test'
                : $dbName.'_'.$this->getDbNameEnvValue();
        }

        return $dbName;
    }

    protected function issetDbNameEnvValue(): bool
    {
        $dbName = $this->getDbNameEnvValue();

        return !empty($dbName);
    }

    protected function getDbNameEnvValue(): ?string
    {
        $dbName = getenv(EnvCommandCreator::ENV_TEST_CHANNEL_READABLE);

        return is_string($dbName) ? $dbName : null;
    }
}
