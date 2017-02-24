<?php

namespace Zazalt\Databaser\Tests;

use Zazalt\Databaser\Databaser;

class DatabaserTest extends \Zazalt\Databaser\Tests\ZazaltTest
{
    protected $that;

    public function __construct()
    {
        parent::loader(Databaser::class);
    }
}