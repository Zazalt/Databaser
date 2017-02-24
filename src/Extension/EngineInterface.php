<?php

namespace Zazalt\Databaser\Extension;

interface EngineInterface
{
    public function connect();
    public function run();
    public function getTables();
    public function getTableRows(string $tableName);
}