<?php

namespace Zazalt\Databaser\Extension;

interface EngineInterface
{
    public function connect();
    public function getTables();
    public function getTableRows($tableName);
    public function run();
}