<?php

declare(strict_types=1);

namespace Liuggio\Fastest\Trait;

use Liuggio\Fastest\Process\EnvCommandCreator;

trait ServerDataTrait
{
    /**
     * @return array<scalar>
     */
    protected function getServerWithDecomposeArgv(): array
    {
        $server = $_SERVER;

        if (isset($server['argv']) && is_array($server['argv'])) {
            $mergedArgs = array_fill_keys(
                array_map('strtolower', array_keys($server)),
                true
            );
            EnvCommandCreator::decomposeRecursively('argv', $server['argv'], $server, $mergedArgs);
            unset($server['argv']);
        }

        return $server;
    }
}