Fastest - simple parallel testing execution
===========================================

[![Build Status](https://secure.travis-ci.org/liuggio/fastest.png?branch=master)](http://travis-ci.org/liuggio/fastest)
[![Latest Stable Version](https://poser.pugx.org/liuggio/fastest/v/unstable.png)](https://packagist.org/packages/liuggio/fastest)

## Only one thing

**Execute parallel testing, creating a Process for each Processor (with some goodies for functional tests).**

``` bash
find tests/ -name "*Test.php" | ./bin/fastest "bin/phpunit -c app {};"
```

This tool works with **any testing tool available**! It just executes it in parallel.

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
5. Increase Verbosity with -v option.

## How

It creates N threads where N is the number of the core in the computer.

100% written in PHP, inspired by [Parallel](https://github.com/grosser/parallel).

## Simple usage

### Piping tests

I suggest to use pipe:

``` bash
find tests/ -name "*Test.php" | ./bin/fastest
```

or with `ls`

``` bash
ls -d test/* | ./bin/fastest
```

calling with placeholders:

``` bash
find tests/ -name "*Test.php" | ./bin/fastest "/my/path/phpunit -c app {};"
```

`{}` is the current test file.
`{p}` is the current process number.

### Using the phpunit.xml.dist as input

You can use the option `-x` and import the test suites from the `phpunit.xml.dist`

`./bin/fastest -x phpunit.xml.dist`

If you use this option make sure the test-suites contains a lot of directory, is not suggested.

This function should be improved help needed.

### Functional tests and database

Inside your tests you could use the env. variables,

Image that you are running tests on a computer that has 4 core, `fastest` will create 4 threads in parallel,
and inside your test you could use those variables:

``` php
echo getevn('ENV_TEST_CHANNEL_READABLE'); // The number of the current channel that is using the current test eg.2
echo getevn('ENV_TEST_CHANNEL_READABLE'); // Name for the database, is a readable name eg. test_2
echo getevn('ENV_TEST_CHANNELS_NUMBER');  // Max channel number on a system (the core number) eg. 4
echo getevn('ENV_TEST_ARGUMENT');         // The current running test eg. tests/UserFunctionalTest.php
echo getevn('ENV_TEST_INC_NUMBER');       // Unique number of the current test eg. 32
echo getevn('ENV_TEST_IS_FIRST');         // Is 1 if is the first test on its thread useful for clear cache.
```

### Setup the database `before`

you can also run a script per process **before** the tests, useful for init schema and fixtures loading.

``` bash
find tests/ -name "*Test.php" | ./bin/fastest -b"app/console doc:sch:create -e test";
```

### The arguments:

```
Usage:
 fastest [-p|--process="..."] [-b|--before="..."] [-x|--xml="..."] [-o|--preserve-order] [execute]

Arguments:
 execute               Optional command to execute.

Options:
 --process (-p)        Number of parallel processes, default: available CPUs.
 --before (-b)         Execute a process before consuming the queue, it executes this command once per process, useful for init schema and load fixtures.
 --xml (-x)            Read input from a phpunit xml file from the '<testsuites>' collection. Note: it is not used for consuming.
 --preserve-order (-o) Queue is randomized by default, with this option the queue is read preserving the order.
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.

```

## Symfony and Doctrine DBAL Adapter

If you want to parallel functional tests, and if you have a machine with 4 CPUs, the best think you could do is create a db foreach parallel process,
`fastest` gives you the opportunity to work easily with Symfony.

Modifying the config_test config file in Symfony, each functional test will look for a database called `test_x` automatically (x is from 1 to CPUs number).

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

**Easy** see [.travis.yml](.travis.yml#L14) file

### TODO

- ~~Rerun only failed tests~~ Done!
- ~~Add the db_name variable~~ Done!
- ~~Remove redis ad dependency~~ Done!
- ~~Remove parallel_tests ad dependency~~ Done!
- Behat provider?
- Develop ProcessorCounter for Windows/Darwin.
- Improve the UI and Progress bar.
