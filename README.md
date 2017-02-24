Databaser
=================

[![Build Status](https://travis-ci.org/Zazalt/Databaser.svg?branch=master)](https://travis-ci.org/Zazalt/Databaser)
[![Coverage Status](https://coveralls.io/repos/github/Zazalt/Databaser/badge.svg?branch=master)](https://coveralls.io/github/Zazalt/Databaser?branch=master)
[![Code Climate](https://codeclimate.com/github/Zazalt/Databaser/badges/gpa.svg)](https://codeclimate.com/github/Zazalt/Databaser)
[![Issue Count](https://codeclimate.com/github/Zazalt/Databaser/badges/issue_count.svg)](https://codeclimate.com/github/Zazalt/Databaser/issues)
[![Total Downloads](https://poser.pugx.org/zazalt/databaser/downloads)](https://packagist.org/packages/zazalt/databaser/stats)
[![Latest Stable Version](https://poser.pugx.org/zazalt/databaser/v/stable)](https://packagist.org/packages/zazalt/databaser)
![Version](https://img.shields.io/badge/version-beta-yellow.svg)

Databaser is a PHP library/package to 'translate' a database to array.

Requirements
---------------
* php >= 7.1.0

Packagist Dependencies
---------------
* [zazalt/omen](https://github.com/zazalt/omen)

Installation
---------------
With composer:
``` json
{
	"require": {
		"zazalt/databaser": "dev-master"
	}
}
```

## Usage
```php
/**
 * Will return an array with all tables/entities and rows
 */
print_r(
    Zazalt\Databaser\Databaser::getInstance()
        ->setEngine(\Zazalt\Omen\Omen::ENGINE_POSTGRESQL)
        //->setHost('127.0.0.1') // If not set, default is 127.0.0.1
        //->setPort()            // If not set, default is 5432 (PostgreSQL default port)
        ->setUsername('your_username')
        ->setPassword('your_password')
        ->setDatabase('your_database_name')
        ->run()
);
```