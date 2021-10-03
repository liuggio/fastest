Fastest - simple parallel testing execution
===========================================

![example branch parameter](https://github.com/liuggio/fastest/actions/workflows/build.yml/badge.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/liuggio/fastest/v/stable.svg)](https://packagist.org/packages/liuggio/fastest) [![Latest Unstable Version](https://poser.pugx.org/liuggio/fastest/v/unstable.svg)](https://packagist.org/packages/liuggio/fastest)

## Only one thing

**Execute parallel commands, creating a Process for each Processor (with some goodies for functional tests).**

``` bash
find tests/ -name "*Test.php" | ./vendor/liuggio/fastest/fastest "vendor/phpunit/phpunit/phpunit -c app {};"
```

Fastest works with **any available testing tool**! It just executes it in parallel.

It is optimized for functional tests, giving an easy way to work with N databases in parallel.

## Motto

> "I had a problem,  
>  so I decided to use threads.  
>  tNwoowp rIo bhlaevmes.

## Why

We were tired of not being able to run [paratest](https://github.com/brianium/paratest) with our project (big complex functional project).  
[Parallel](https://github.com/grosser/parallel) is a great tool but not so nice for functional tests.  
There were no simple tool available for functional tests.

Our old codebase run in 30 minutes, now in 7 minutes with 4 Processors.

## Features

1. Functional tests could use a database per processor using the environment variable.
2. Tests are randomized by default.
3. Is not coupled with PhpUnit you could run any command.
3. Is developed in PHP with no dependencies.
4. As input you could use a `phpunit.xml.dist` file or use pipe (see below).
5. Includes a Behat extension to easily pipe scenarios into fastest.
6. Increase Verbosity with -v option.
7. Works with a installation in project or global mode

## How

It creates N threads where N is the number of the core in the computer.  
Really fast,
100% written in PHP, inspired by [Parallel](https://github.com/grosser/parallel).

## Usage

### Configure paths to binaries

Examples shown below use paths to binaries installed in the `vendor/` directory.
You can use symlinks in the `bin/` directory by defining the [`bin-dir` parameter of Composer](https://getcomposer.org/doc/06-config.md#bin-dir):

```
composer config "bin-dir" "bin"
```

Then you'll be able to call binaries in the `bin/` directory:

- `bin/fastest` instead of `vendor/liuggio/fastest/fastest`
- `bin/phpunit` instead of `vendor/phpunit/phpunit/phpunit`

### Parallelize everything

``` bash
ls | ./fastest "echo slow operation on {}" -vvv
```
#### Using the placeholders

`{}` is the current test file.  
`{p}` is the current processor number.  
`{n}` is the unique number of the current test.
`phpunit {}` is used as default command.

### PHPUnit

#### A. Using `ls`, list of folders as input **suggested**

``` bash
ls -d test/* | ./vendor/liuggio/fastest/fastest "vendor/phpunit/phpunit/phpunit {};"
```

#### B. using `find`, list of php files as input

``` bash
find tests/ -name "*Test.php" | ./vendor/liuggio/fastest/fastest  "vendor/phpunit/phpunit/phpunit {};"
```

#### C. Using `phpunit.xml.dist` as input

You can use the option `-x` and import the test suites from the `phpunit.xml.dist`

`./vendor/liuggio/fastest/fastest -x phpunit.xml.dist "vendor/phpunit/phpunit/phpunit {};"`

If you use this option make sure the test-suites contains a lot of directories: **this feature should be improved, don't blame help instead.**

### Functional tests and database

Inside your tests you could use the env. variables,  
if you are running tests on a computer that has 4 core, `fastest` will create 4 threads in parallel,
and inside your test you could use those variables to better identify the current process:

``` php
echo getenv('ENV_TEST_CHANNEL');          // The number of the current channel that is using the current test eg.2
echo getenv('ENV_TEST_CHANNEL_READABLE'); // Name used to make the database name unique, is a readable name eg. test_2
echo getenv('ENV_TEST_CHANNELS_NUMBER');  // Max channel number on a system (the core number) eg. 4
echo getenv('ENV_TEST_ARGUMENT');         // The current running test eg. tests/UserFunctionalTest.php
echo getenv('ENV_TEST_INC_NUMBER');       // Unique number of the current test eg. 32
echo getenv('ENV_TEST_IS_FIRST_ON_CHANNEL'); // Is 1 if is the first test on its thread useful for clear cache.
```

### Setup the database `before`

You can also run a script per process **before** the tests, useful for init schema and fixtures loading.

``` bash
find tests/ -name "*Test.php" | ./vendor/liuggio/fastest/fastest -b"app/console doc:sch:create -e test" "vendor/phpunit/phpunit/phpunit {};";
```

### Generate and merge code coverage

``` bash
# Install phpcov in order to merge the code coverage
composer require --dev phpunit/phpcov
# Create a directory where the coverage files will be put
mkdir -p cov/fastest/
# Generate as many files than tests, since {n} is an unique number for each test
find tests/ -name "*Test.php" | vendor/liuggio/fastest/fastest "vendor/phpunit/phpunit/phpunit -c app {} --coverage-php cov/fastest/{n}.cov;"
# Merge the code coverage files
phpcov merge cov/fastest/ --html cov/merge/fastest/
```

Code coverage will be available in the `cov/merge/fastest/` directory.

## Storage adapters

If you want to parallel functional tests, and if you have a machine with 4 CPUs, the best thing you could do is create a db foreach parallel process,
`fastest` gives you the opportunity to work easily with Symfony.

Modifying the `config_test.yml` config file in Symfony, each functional test will look for a database called `<database_name>_test_x` automatically (x is from 1 to CPUs number).

### Doctrine DBAL

`config_test.yml`
``` yml
parameters:
    # Stubs
    doctrine.dbal.connection_factory.class: Liuggio\Fastest\Doctrine\DBAL\ConnectionFactory
```

### Doctrine MongoDB Connection 

`config_test.yml`
``` yml
parameters:
    # Stubs
    doctrine_mongodb.odm.connection.class: Liuggio\Fastest\Doctrine\MongoDB\Connection
```

### SQLite databases

SQLite databases don't have names. It's always 1 database per file. If SQLite driver is detected, instead switching the database name, database path will be changed. To make it work simply add `__DBNAME__` placeholder in your database path.

`config_test.yml`
``` yml
doctrine:
    dbal:
        driver:   pdo_sqlite
        path:     "%kernel.cache_dir%/__DBNAME__.db"
        
parameters:
    doctrine.dbal.connection_factory.class: Liuggio\Fastest\Doctrine\DBAL\ConnectionFactory
```

Where `__DBNAME__` will be replaced with `ENV_TEST_CHANNEL_READABLE` value.

### Behat.* extension

A Behat extension is included that provides the ability for Behat to output a list of feature files or individual scenarios that would be executed without
actually executing them. This list can be piped into fastest to run the scenarios in parallel.

To install the extension just add it to your `behat.yml` file:

``` yaml
extensions:
    Liuggio\Fastest\Behat\ListFeaturesExtension\Extension: ~
```

for Behat2:

``` yaml
extensions:
    Liuggio\Fastest\Behat2\ListFeaturesExtension\Extension: ~
```

After this you will have two additional command line options: `--list-features` and `--list-scenarios`. The former will output a list of *.feature files
and the later will output each scenario of each feature file, including its line number (e.g. /full/path/Features/myfeature.feature:lineNumber)

This will let you pipe the output directly into fastest to parallelize its execution:

    /my/path/behat --list-scenarios | ./vendor/liuggio/fastest/fastest "/my/path/behat {}"

Using `--list-scenarios` is preferred over `--list-features` because it will give a more granular scenario-by-scenario output, allowing fastest to shuffle and balance
individual tests in a better way.

### About browser-based tests (Selenium, Mink, etc)

When a browser is controlled remotely via PHPUnit, Behat or another test suite that is being used by Fastest, the browser makes requests
back to the server. The problem is that when the server process the request it has no idea of which fastest channel called it, so there must
be a way to set this information before connecting to the database (in order to choose the correct database that corresponds to the channel).

One possible way is to implement the following steps:

#### 1. Set a cookie, GET query parameter or HTTP header with the appropiate channel value

When your test scenario begins, maybe at the authentication phase, set one of the following to the value of the environment variable `ENV_TEST_CHANNEL_READABLE`:

* If it's a cookie or a GET query parameter name it ENV_TEST_CHANNEL_READABLE
   * Beware that if you use the GET query parameter option and via automation you click on a link of the browser that doesn't have that query parameter, the request won't
   have the query parameter the server won't know the channel to initialize.
* If it's a HTTP header name it X-FASTEST-ENV-TEST-CHANNEL-READABLE and send it on every request to the server.

#### 2. Configure the entry point of your application to set the environment variables for the request

For this is enough to add the following code before booting your application:

    \Liuggio\Fastest\Environment\FastestEnvironment::setFromRequest();

This will detect the presence of the ENV_TEST_CHANNEL_READABLE value in any of the contexts mentioned in #1 and set the corresponding environment variable.

For example, in the case of the Symfony framework you may just add it in `web/app_dev.php` just before `require_once __DIR__.'/../app/AppKernel.php'`:

``` php
// ... code
$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

\Liuggio\Fastest\Environment\FastestEnvironment::setFromRequest();

require_once __DIR__.'/../app/AppKernel.php';
$kernel = new AppKernel('dev', true);
// ... code
```

## Install

If you use Composer just run `composer require --dev 'liuggio/fastest:^1.6'`

or simply add a dependency on liuggio/fastest to your project's composer.json file:

	{
	    "require-dev": {
		    "liuggio/fastest": "^1.6"
	    }
	}

For a system-wide installation via Composer, you can run:

`composer global require "liuggio/fastest=^1.6"`

Make sure you have `~/.composer/vendor/bin/` in your path,
read more at [getcomposer.org](https://getcomposer.org/doc/00-intro.md#globally)

If you want to use it with phpunit you may want to install phpunit/phpunit as dependency.

### Run this test with `fastest`

```
Usage:
 fastest [-p|--process="..."] [-b|--before="..."] [-x|--xml="..."] [-o|--preserve-order] [--no-errors-summary] [execute]

Arguments:
 execute               Optional command to execute.

Options:
 --process (-p)        Number of parallel processes, default: available CPUs.
 --before (-b)         Execute a process before consuming the queue, it executes this command once per process, useful for init schema and load fixtures.
 --xml (-x)            Read input from a phpunit xml file from the '<testsuites>' collection. Note: it is not used for consuming.
 --preserve-order (-o) Queue is randomized by default, with this option the queue is read preserving the order.
 --rerun-failed (-r)   Re-run failed test with before command if exists.
 --no-errors-summary   Do not display all errors after the test run. Useful with --vv because it already displays errors immediately after they happen.
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.

```

e.g. `./fastest -x phpunit.xml.dist -v "bin/phpunit {}"`

### Known problems

If you're faceing problems with unknown command errors, make sure your  [variables-order](http://us.php.net/manual/en/ini.core.php#ini.variables-order) `php.ini` setting contains `E`. If not, your enviroment variables are not set, and commands that are in your `PATH` will not work.

### Contribution

Please help with code, love, feedback and bug reporting.

Thanks to: 
 
- @giorrrgio for the mongoDB adapter
- @diegosainz for the Behat2 adapter
- you?


### License [![License](https://poser.pugx.org/liuggio/fastest/license.svg)](https://packagist.org/packages/liuggio/fastest)

Read [LICENSE](./LICENSE) for more information.
