Fastest
=======

[![Build Status](https://secure.travis-ci.org/liuggio/fastest.png?branch=master)](http://travis-ci.org/liuggio/fastest)
[![Total Downloads](https://poser.pugx.org/liuggio/fastest/downloads.png)](https://packagist.org/packages/liuggio/fastest)
[![Latest Stable Version](https://poser.pugx.org/liuggio/fastest/v/stable.png)](https://packagist.org/packages/liuggio/fastest)


### What

This library does only one thing and would like to do it well:

**Execute tests in parallel, one for each CPUs, giving goodies for functional tests.**

### Why

We were tired of not being able to run `paratest` with our project,
Our old codebase runs in 30 minutes, now in 8 mins.

### How

The process is really simple, there are two phases, the first is putting  all the tests in the a queue,
the other is consuming in parallel one test per CPU.

### Over-engineering

It uses redis by default.

## Simple usage

#### Piping tests

push into a queue and execute all the tests in your project:

``` bash
find tests/ -name "*Test.php" | php fastest.php parallel
```
#### Using phpunit.xml.dist

You can use importing the test suites from the `phpunit.xml.dist`

`php fastest.php -x phpunit.xml.dist`

#### Functional test and database

Each CPU has an Env number

``` php
$dbName = sprintf("test_%d", getenv('TEST_ENV_NUMBER'));
```

## Symfony and Doctrine DBAL Adapter

If you want to parallelize functional tests, and if you have a machine with 4 CPUs, you should create 4 databases and then running the fixture.

Modifying the config_test config file in Symfony, each functional test will look for a database called `test_x` (x is from 1 to CPUs number).

`config_test.yml`
``` yml
parameters:
    # Stubs
    doctrine.dbal.connection_factory.class: Liuggio\Fastest\DbalConnectionFactory
```


### Advanced

Consume one test per time

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

