<?php

namespace Zazalt\Databaser;

class Databaser extends \Zazalt\Databaser\Extension\DatabaserSetters
{
    const ENGINE_POSTGRESQL = 'postgresql';
    const ENGINE_PMYSQL     = 'mysql';

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
            case self::ENGINE_PMYSQL:
                $this->strategy = new \Zazalt\Databaser\Engine\MySQL($this);
                break;

            case self::ENGINE_POSTGRESQL:
                $this->strategy = new \Zazalt\Databaser\Engine\PostgreSQL($this);
                break;
        }

        return $this->strategy->run($this);
    }
}