<?php

declare(strict_types=1);

namespace Liuggio\Fastest\Trait;

trait ServerDataTrait
{
    /**
     * @return array<scalar>
     */
    protected function getServerWithoutArgv(): array
    {
        $server = $_SERVER;
        if (array_key_exists('argv', $server)) {
            unset($server['argv']);
        }
        return $server;
    }
}