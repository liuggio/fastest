Fastest - simple parallel testing execution
===========================================

[![Build Status](https://secure.travis-ci.org/liuggio/fastest.png?branch=master)](http://travis-ci.org/liuggio/fastest)
[![Latest Stable Version](https://poser.pugx.org/liuggio/fastest/v/unstable.png)](https://packagist.org/packages/liuggio/fastest)

This is not stable, things will change... :)

## What

This library does only one simple thing:

**Execute parallel testing, creating a process for each CPU (and with some goodies for functional tests).**

``` bash
find tests/ -name "*Test.php" | php fastest parallel "/my/path/phpunit -c app {};"
```

This is optimized for functional tests, creates a process for each CPU, giving an easy way to work with n databases in parallel.

## Motto

> "I had a problem,

>  so I decided to use threads.

>  tNwoowp rIo bhlaevmes.

## Why

We were tired of not being able to run `paratest` with our project (big complex functional project).

`parallel` is a great tool but for unit tests.

There were no simple tool available for functional tests.

Our old codebase run in 30 minutes, now in 13 minutes with 4 CPU.

## How

100% written in PHP, inspired by parallel.

There's a producer and N. consumers (one per CPU), the Queue has been developed with `PHP msg_*` functions.

### Feature

1. Functional tests could use a database per processor using the environment variable.
2. Tests are randomized by default
3. Is not coupled with PhpUnit you could run any command.
3. Is developed in PHP with no dependencies.
4. As input you could use a `phpunit.xml.dist` file or use pipe (see below).

## Simple usage

### Piping tests

It pushes into a queue and executes all the tests in your project:

``` bash
find tests/ -name "*Test.php" | fastest parallel
```

or with `ls`

``` bash
ls -d test/* | fastest parallel
```

calling with arguments

``` bash
find tests/ -name "*Test.php" | php fastest parallel "/my/path/phpunit -c app {};"
```

`{}` is the current test file.
`{p}` is the current process number.

### Using the phpunit.xml.dist as input

You can use the option `-x` and import the test suites from the `phpunit.xml.dist`

`fastest parallel -x phpunit.xml.dist`

If you use this option make sure the test-suites are smaller as you can.

### Functional tests and database

Each Process has an Env number

``` php
echo getenv('TEST_ENV_NUMBER');        // Current process number eg.2
echo getenv('ENV_TEST_DB_NAME');       // Name for the database  eg. test_2
echo getevn('ENV_TEST_MAX_PROCESSES'); // Number of CPUs on the system eg. 4
```

### Setup the database `before`

you can also run a script per process **before** the tests, useful for init schema and fixtures loading.

``` bash
find tests/ -name "*Test.php" | fastest parallel -b"app/console doc:sch:create -e test";
```

### The arguments:

```
Usage:
 parallel [-p|--process="..."] [-b|--before="..."] [-x|--xml="..."] [-o|--preserve-order] [-k|--queue-key="..."] [execute]

Arguments:
 execute               Optional command to execute.

Options:
 --process (-p)        Number of process, default: available CPUs.
 --before (-b)         Execute a process before consuming the queue, execute it once per Process, useful for init schema and fixtures.
 --xml (-x)            Read input from a phpunit xml file from the '<testsuites>' collection. Note: it is not used for consuming.
 --preserve-order (-o) Queue is randomized by default, with this option the queue is read preserving the order.
 --queue-key (-k)      Queue key number.
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
```

## Symfony and Doctrine DBAL Adapter

If you want to parallel functional tests, and if you have a machine with 4 CPUs, you should create 4 databases and then running the fixture.

Modifying the config_test config file in Symfony, each functional test will look for a database called `test_x` (x is from 1 to CPUs number).

`config_test.yml`
``` yml
parameters:
    # Stubs
    doctrine.dbal.connection_factory.class: Liuggio\Fastest\Doctrine\DbalConnectionFactory
```

## Install


if you use Composer just run `composer require-dev 'liuggio/fastest' 'dev-master'`

or simply add a dependency on liuggio/fastest to your project's composer.json file:

	{
	    "require-dev": {
		"liuggio/fastest": "dev-master"
	    }
	}

For a system-wide installation via Composer, you can run:

`composer global require "liuggio/fastest=dev-master"`

Make sure you have `~/.composer/vendor/bin/` in your path,
read more at [getcomposer.org](https://getcomposer.org/doc/00-intro.md#globally)

If you want to use it with phpunit you may want to install phpunit/phpunit as dependency.

### Run this test with `fastest`

**Easy** see [.travis.yml](.travis.yml) file

### TODO

- Rerun only failed tests
- ~~Add the db_name variable~~ Done!
- ~~Remove redis ad dependency~~ Done!
- ~~Remove parallel_tests ad dependency~~ Done!
- Behat provider?
- Develop ProcessorCounter for Windows/Darwin.
- Improve the Progress bar.