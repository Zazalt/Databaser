<?php

namespace Zazalt\Databaser;

use Zazalt\Omen\Omen;

class Databaser extends \Zazalt\Databaser\Extension\DatabaserSetters
{
    private $strategy;

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return $this
     */
    public static function getInstance()
    {
        if(null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function run()
    {
        switch($this->engine) {
            case Omen::ENGINE_MYSQL:
                $this->strategy = new \Zazalt\Databaser\Engine\MySQL($this);
                break;

            case Omen::ENGINE_POSTGRESQL:
                $this->strategy = new \Zazalt\Databaser\Engine\PostgreSQL($this);
                break;
        }

        return $this->strategy->run($this);
    }
}