Fastest - simple parallel testing execution
===========================================

[![Build Status](https://secure.travis-ci.org/liuggio/fastest.png?branch=master)](http://travis-ci.org/liuggio/fastest)
[![Latest Stable Version](https://poser.pugx.org/liuggio/fastest/v/unstable.png)](https://packagist.org/packages/liuggio/fastest)

**NOT STABLEEEEEE**

## What

This library does only one simple thing:

**Execute tests in parallel, one for each CPUs (now with goodies for functional tests).**

## Motto

> "I had a problem,

>  so I decided to use threads.

>  tNwoowp rIo bhlaevmes.

## Why

We were tired of not being able to run `paratest` with our project (big complex functional project).

There were no simple tool available for functional tests.

Our old codebase run in 30 minutes, now in 13 minutes.

## How

There's a producer and n consumers (one per CPU), the queue has been developed in ... Redis.

**Over-engineering?**

The stable version will not have dependencies like Redis...
Developer time is a cost, tests are a complex world, if you want you could replace Redis changing queue see [Queue/Infrastructure](./src/Queue/Infrastructure).

## Simple usage

#### Piping tests

It pushes into a queue and executes all the tests in your project:

``` bash
find tests/ -name "*Test.php" | php fastest.php parallel
```

or with `ls`

``` bash
ls -d test/* | php fastest.php parallel
```

calling with arguments

``` bash
php src/fastest.php parallel "/my/path/phpunit -c app {};"
```

#### Using phpunit.xml.dist

You can use the option `-x` and import the test suites from the `phpunit.xml.dist`

`php fastest.php parallel -x phpunit.xml.dist`

#### Functional test and database

Each CPU has an Env number

``` php
echo getenv('TEST_ENV_NUMBER');
```

you can also run one script per CPU **before** the tests, useful for init schema and fixtures loading.

``` bash
find tests/ -name "*Test.php" | php fastest.php parallel -b"app/console doc:sch:create -e test";
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

## Tail the log

This library uses monolog, the command is logged to `sys_get_temp_dir().'/'.fastest.log`

### Advanced

#### Parameters

if you need to change the redis port or the log directory just use in bash
the export command, the parameters are:

1. LOG_PATH
2. LOG_LEVEL
3. REDIS_HOSTAME
4. REDIS_PORT
5. REDIS_QUEUE

eg.
``` bash
export LOG_PATH=/tmp/a.log;
```

#### Only input working as Producer

`find tests/ -name "*Test.php" | php src/fastest.php parallel -i`

#### Run something different

`php src/fastest.php parallel "phpunit -c app {};"`

or
`php src/fastest.php parallel "echo {};"`

#### Consume one test per time

`php src/fastest.php consume`

is the same as

`php src/fastest.php consume "phpunit {}"`

{} is the value of the queue.

eg:
`php src/fastest.php consume "echo {}"`
will print and remove one element from the queue.

with the `--loop` option all the queue will be consumed.

## Install

**Require**

it uses by default Redis for queue and parallel_tests of parallelize.

``` bash
sudo apt-get install redis-server
sudo gem install parallel_tests
```

### composer

`composer require-dev 'liuggio/fastest' 'dev-master'`

### Run this test

see [.travis.yml](.travis.yml) file

### TODO

- Rerun only failed tests
- Add the db_name variable
- Remove parallel_tests ad dependency
- Remove redis ad dependency
- Behat provider?
