<?php

namespace Zazalt\Databaser\Extension;

class DatabaserSetters
{
    public $engine;
    public $host        =   '127.0.0.1';
    public $port        =   5432;
    public $username    =   null;
    public $password    =   null;
    public $database    =   null;

    /**
     * @param $host string
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param $port integer
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param $engine   string
     * @return $this
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @param $username string
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param $password string
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param $database string
     * @return $this
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }
}