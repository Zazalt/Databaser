<?php

namespace Zazalt\Databaser\Engine;

class PostgreSQL implements \Zazalt\Databaser\Extension\EngineInterface
{
    private $entityMaker;
    private $connection;

    private $tables;
    private $entities;

    public function __construct($entityMaker)
    {
        $this->entityMaker = $entityMaker;
    }

    public function connect()
    {
        $dsn = "pgsql:host={$this->entityMaker->host};port={$this->entityMaker->port};dbname={$this->entityMaker->database};user={$this->entityMaker->username};password={$this->entityMaker->password}";

        $this->connection = new \PDO($dsn);
    }

    public function run()
    {
        $this->connect();

        // Populate entities
        foreach($this->getTables() as $table) {
            $this->entities[$table['table_name']] = [];
        }

        foreach($this->entities as $entityName => $entity) {
            foreach($this->getTableRows($entityName) as $row) {

                $default = null;
                $userDefined = null;
                if(preg_match('/::/i', $row['column_default']) && !preg_match('/nextval\(/i', $row['column_default'])) {
                    preg_match("/'(.*)'::(.*)/i", $row['column_default'], $maches);
                    $default = $maches[1];

                    $userDefined = $this->getUserDefinedTypes($maches[2], '{#}');
                    if(is_array($userDefined) && count($userDefined) > 0) {
                        $userDefined = explode('{#}', $userDefined['elements']);
                    }

                } else if(!preg_match('/nextval\(/i', $row['column_default'])) {
                    $default = $row['column_default'];
                }

                $this->entities[$entityName][$row['column_name']] = [
                    'type'                      =>  (preg_match('/timestamp /i', $row['data_type']) ? $row['udt_name'] : $row['data_type']),
                    'characterMaximumLength'    =>  ($row['data_type'] == 'text' ? $row['character_octet_length'] : $row['character_maximum_length']),
                    'default'                   =>  $default,
                    'userDefined'               =>  $userDefined,
                    'primaryKey'                =>  $this->checkPrimaryKey($entityName, $row['column_name']),
                    'unique'                    =>  $this->checkUniqueKey($entityName, $row['column_name']),
                    'foreignKey'                =>  $this->checkForeignKey($entityName, $row['column_name']),
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
        $statement = $this->connection->prepare("SELECT * FROM information_schema.tables WHERE table_catalog = :database AND table_schema = 'public'");
        $statement->execute([
            ':database' => $this->entityMaker->database
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all rows for all tables
     */
    public function getTableRows($tableName)
    {
        $statement = $this->connection->prepare("SELECT * FROM information_schema.columns WHERE table_catalog = :database AND table_name = :table");
        $statement->execute([
            ':database' => $this->entityMaker->database,
            ':table'    => $tableName
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function checkPrimaryKey($tableName, $rowName)
    {
        $foreignKeys = $this->getForeignKeys($tableName, $rowName);

        foreach($foreignKeys as $foreignKey) {
            $statement = $this->connection->prepare("SELECT * FROM information_schema.table_constraints WHERE table_name = :table AND constraint_name = :foreignKey AND constraint_type = 'PRIMARY KEY'");
            $statement->execute([
                ':table'        => $tableName,
                ':foreignKey'   => $foreignKey['constraint_name'],
            ]);

            $result = $statement->fetch(\PDO::FETCH_ASSOC);

            return (is_array($result) && count($result) > 0 ? 1 : 0);
        }

        return 0;
    }

    public function checkUniqueKey($tableName, $rowName)
    {
        $foreignKeys = $this->getForeignKeys($tableName, $rowName);

        foreach($foreignKeys as $foreignKey) {
            $statement = $this->connection->prepare("SELECT * FROM information_schema.table_constraints WHERE table_name = :table AND constraint_name = :foreignKey AND constraint_type = 'UNIQUE'");
            $statement->execute([
                ':table'        => $tableName,
                ':foreignKey'   => $foreignKey['constraint_name'],
            ]);

            $result = $statement->fetch(\PDO::FETCH_ASSOC);

            return (is_array($result) && count($result) > 0 ? 1 : 0);
        }

        return 0;
    }

    public function checkForeignKey($tableName, $rowName)
    {
        $foreignKeys = $this->getForeignKeys($tableName, $rowName);

        foreach($foreignKeys as $foreignKey) {
            $statement = $this->connection->prepare("SELECT * FROM information_schema.table_constraints WHERE table_name = :table AND constraint_name = :foreignKey AND constraint_type = 'FOREIGN KEY'");
            $statement->execute([
                ':table'        => $tableName,
                ':foreignKey'   => $foreignKey['constraint_name'],
            ]);

            $result = $statement->fetch(\PDO::FETCH_ASSOC);

            if(is_array($result) && count($result) > 0) {

                $statement = $this->connection->prepare("SELECT * FROM information_schema.constraint_column_usage WHERE constraint_catalog = :database AND constraint_name = :foreignKey");
                $statement->execute([
                    ':database'     => $this->entityMaker->database,
                    ':foreignKey'   => $foreignKey['constraint_name'],
                ]);
                $result = $statement->fetch(\PDO::FETCH_ASSOC);

                $return = [
                    'name'      =>  $result['constraint_name'],
                    'table'     =>  $result['table_name'],
                    'row'       =>  $result['column_name']
                ];

                $statement = $this->connection->prepare("SELECT * FROM information_schema.referential_constraints WHERE constraint_catalog = :database AND constraint_name = :foreignKey");
                $statement->execute([
                    ':database'     => $this->entityMaker->database,
                    ':foreignKey'   => $foreignKey['constraint_name'],
                ]);
                $result = $statement->fetch(\PDO::FETCH_ASSOC);

                $return = array_merge($return, [
                    'onUpdate'  =>  $result['update_rule'],
                    'onDelete'  =>  $result['delete_rule']
                ]);

                return $return;
            }
        }
    }

    public function getForeignKeys($tableName, $rowName)
    {
        $statement = $this->connection->prepare("SELECT * FROM information_schema.key_column_usage WHERE table_name = :table AND column_name = :row");
        $statement->execute([
            ':table'    => $tableName,
            ':row'      => $rowName
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUserDefinedTypes($userDefinedName, $separator)
    {
        // http://dba.stackexchange.com/questions/35497/display-user-defined-types-and-their-details
        $statement = $this->connection->prepare("SELECT n.nspname AS schema,
                        pg_catalog.format_type ( t.oid, NULL ) AS name,
                        t.typname AS internal_name,
                        CASE
                            WHEN t.typrelid != 0
                            THEN CAST ( 'tuple' AS pg_catalog.text )
                            WHEN t.typlen < 0
                            THEN CAST ( 'var' AS pg_catalog.text )
                            ELSE CAST ( t.typlen AS pg_catalog.text )
                        END AS size,
                        pg_catalog.array_to_string (
                            ARRAY( SELECT e.enumlabel
                                    FROM pg_catalog.pg_enum e
                                    WHERE e.enumtypid = t.oid
                                    ORDER BY e.oid ), E'{$separator}'
                            ) AS elements,
                        pg_catalog.obj_description ( t.oid, 'pg_type' ) AS description
                    FROM pg_catalog.pg_type t
                    LEFT JOIN pg_catalog.pg_namespace n
                        ON n.oid = t.typnamespace
                    WHERE
                        pg_catalog.format_type ( t.oid, NULL ) = :name
                        AND ( t.typrelid = 0
                            OR ( SELECT c.relkind = 'c'
                                    FROM pg_catalog.pg_class c
                                    WHERE c.oid = t.typrelid
                                )
                        )
                        AND NOT EXISTS
                            ( SELECT 1
                                FROM pg_catalog.pg_type el
                                WHERE el.oid = t.typelem
                                    AND el.typarray = t.oid
                            )
                        AND n.nspname <> 'pg_catalog'
                        AND n.nspname <> 'information_schema'
                        AND pg_catalog.pg_type_is_visible ( t.oid )
                    ORDER BY 1, 2");
        $statement->execute([
            ':name'    => $userDefinedName
        ]);
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }
}