<?php

/*
 * The contents of this file comes from behat's Hooks file (features/bootstrap/Hooks.php) under the following license:
 *
 *  Copyright (c) 2011-2013 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 *  Permission is hereby granted, free of charge, to any person
 *  obtaining a copy of this software and associated documentation
 *  files (the "Software"), to deal in the Software without
 *  restriction, including without limitation the rights to use,
 *  copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the
 *  Software is furnished to do so, subject to the following
 *  conditions:
 *
 *  The above copyright notice and this permission notice shall be
 *  included in all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 *  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 *  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 *  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 *  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 *  OTHER DEALINGS IN THE SOFTWARE.
 *
 */

use Behat\Behat\Context\Context;
use Liuggio\Fastest\Process\EnvCommandCreator;

/**
 * Based on Behat test suite hooks (some portions of the code that didn't apply were removed and others added).
 */
class Hooks implements Context
{
    /**
     * Get current fastest channel.
     *
     * When running under fastest returns the channel (starting at 1)
     * If not, will return 0.
     *
     * @return int
     */
    public static function getFastestChannel()
    {
        $channel = getenv(EnvCommandCreator::ENV_TEST_CHANNEL);

        return (int) $channel;
    }

    /**
     * @BeforeSuite
     *
     * Cleans test folders in the temporary directory.
     */
    public static function cleanTestFolders()
    {
        $channel = self::getFastestChannel();

        $underFastest = (bool) $channel;
        $firstFastestTest = getenv(EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL);

        // If under fastest only purge directory on the first test
        if ($underFastest && !$firstFastestTest) {
            return;
        }

        if (is_dir($dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fastest-behat'.$channel)) {
            self::rmdirRecursive($dir);
        }
    }

    /**
     * @BeforeScenario
     *
     * Prepares test folders in the temporary directory.
     */
    public function prepareTestFolders()
    {
        $channel = self::getFastestChannel();

        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fastest-behat'.$channel.DIRECTORY_SEPARATOR.
            md5(microtime(true) * rand(0, 10000));

        mkdir($dir, 0777, true);
        chdir($dir);

        mkdir('features');
        mkdir('features'.DIRECTORY_SEPARATOR.'bootstrap');
    }

    /**
     * Removes files and folders recursively at provided path.
     *
     * @param string $path
     */
    private static function rmdirRecursive($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path.DIRECTORY_SEPARATOR.$file;
            if (is_dir($file)) {
                self::rmdirRecursive($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}
