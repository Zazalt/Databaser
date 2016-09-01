<?php

/**
 * Will return an array with all tables/entities and rows
 */
$entities = Zazalt\Databaser\Databaser::getInstance()
                ->setEngine(Zazalt\Databaser\Databaser::ENGINE_POSTGRESQL)
                //->setHost('127.0.0.1') // If not set, default is 127.0.0.1
                //->setPort()            // If not set, default is 5432 (PostgreSQL default port)
                ->setUsername('your_username')
                ->setPassword('your_password')
                ->setDatabase('your_database_name')
                ->run();