<?php

namespace Zazalt\Databaser\Engine;

use Zazalt\Databaser\Databaser;

class MySQL implements \Zazalt\Databaser\Extension\EngineInterface
{
    private $Databaser;
    private $connection;
    private $entities;

    public function __construct(Databaser $Databaser)
    {
        $this->Databaser = $Databaser;
    }

    public function connect()
    {
        $dsn = "mysql:host={$this->Databaser->host};port={$this->Databaser->port};dbname={$this->Databaser->database}";
        $this->connection = new \PDO($dsn, $this->Databaser->username, $this->Databaser->password);
    }

    public function run()
    {
        $this->connect();

        // Populate entities
        foreach ($this->getTables() as $table) {
            $this->entities[$table['TABLE_NAME']] = [];
        }

        foreach ($this->entities as $entityName => $entity) {
            foreach ($this->getTableRows($entityName) as $row) {
                $default = null;
                $userDefined = null;

                if(preg_match('/enum\(/i', $row['COLUMN_TYPE'])) {
                    preg_match_all("/'([^\']+)'/i", $row['COLUMN_TYPE'], $maches);
                    $userDefined = $maches[1];
                }

                $this->entities[$entityName][$row['COLUMN_NAME']] = [
                    'type'                      =>  (preg_match('/timestamp /i', $row['DATA_TYPE']) ? $row['udt_name'] : $row['DATA_TYPE']),
                    'characterMaximumLength'    =>  ($row['DATA_TYPE'] == 'text' ? $row['CHARACTER_OCTET_LENGTH'] : $row['CHARACTER_MAXIMUM_LENGTH']),
                    'default'                   =>  $row['COLUMN_DEFAULT'],
                    'userDefined'               =>  $userDefined,
                    'primaryKey'                =>  ($row['COLUMN_KEY'] == 'PRI'),
                    'unique'                    =>  ($row['COLUMN_KEY'] == 'UNI'),
                    'foreignKey'                =>  '',
                ];
            }
        }

        return $this->entities;
    }

    /**
     * Get all tables for a database
     */
    public function getTables()
    {
        $statement = $this->connection->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database");
        $statement->execute([
            ':database' => $this->Databaser->database
        ]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all rows for all tables
     */
    public function getTableRows(string $tableName)
    {
        $statement = $this->connection->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table");
        $statement->execute([
            ':database' => $this->Databaser->database,
            ':table'    => $tableName
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}