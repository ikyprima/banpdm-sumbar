<?php

namespace Modules\Admin\database\Schema;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table as DoctrineTable;
use Doctrine\DBAL\DriverManager;

class Table extends DoctrineTable
{
    private static $conn;
    public static function make($table)
    {
        if (!is_array($table)) {
            $table = json_decode($table, true);
        }

        $name = Identifier::validate($table['name'], 'Table');

        $columns = [];
        foreach ($table['columns'] as $columnArr) {
            $column = Column::make($columnArr, $table['name']);
            $columns[$column->getName()] = $column;
        }

        $indexes = [];
        foreach ($table['indexes'] as $indexArr) {
            $index = Index::make($indexArr);
            $indexes[$index->getName()] = $index;
        }

        // $foreignKeys = [];
        // foreach ($table['foreignKeys'] as $foreignKeyArr) {
        //     $foreignKey = ForeignKey::make($foreignKeyArr);
        //     $foreignKeys[$foreignKey->getName()] = $foreignKey;
        // }
        $foreignKeys = [];
        if (!empty($table['foreignKeys'])) {
            foreach ($table['foreignKeys'] as $fk) {
                $foreignKey = new \Doctrine\DBAL\Schema\ForeignKeyConstraint(
                    $fk['localColumns'],
                    $fk['foreignTable'],
                    $fk['foreignColumns'],
                    $fk['name'],
                    $fk['options'] ?? []
                );
                $foreignKeys[$foreignKey->getName()] = $foreignKey;
            }
        }
        $options = $table['options'];

        return new self($name, $columns, $indexes,[], $foreignKeys, $options);
    }
    public static function getConnection()
    {
        if (self::$conn === null) {
            $config = new \Doctrine\DBAL\Configuration();

            $connectionParams = [
                'dbname'   => config('database.connections.mysql.database'),
                'user'     => config('database.connections.mysql.username'),
                'password' => config('database.connections.mysql.password'),
                'host'     => config('database.connections.mysql.host'),
                'port'     => config('database.connections.mysql.port', 3306),
                'driver'   => self::mapDoctrineDriver(config('database.default')), 
                'charset'  => 'utf8mb4',
            ];

            self::$conn = DriverManager::getConnection($connectionParams, $config);
        }

        return self::$conn;
    }
    protected static function mapDoctrineDriver($laravelDriver)
    {
        return match ($laravelDriver) {
            'mysql'      => 'pdo_mysql',
            'pgsql'      => 'pdo_pgsql',
            'sqlite'     => 'pdo_sqlite',
            'sqlsrv'     => 'pdo_sqlsrv',
            default      => throw new \Exception("Unsupported driver: $laravelDriver"),
        };
    }
    public function getColumnsIndexes($columns, $sort = false)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $matched = [];

        foreach ($this->getIndexes() as $index) {
            if ($index->spansColumns($columns)) {
                $matched[$index->getName()] = $index;
            }
        }

        if (count($matched) > 1 && $sort) {
            // Sort indexes based on priority: PRI > UNI > IND
            uasort($matched, function ($index1, $index2) {
                $index1_type = Index::getType($index1);
                $index2_type = Index::getType($index2);

                if ($index1_type == $index2_type) {
                    return 0;
                }

                if ($index1_type == Index::PRIMARY) {
                    return -1;
                }

                if ($index2_type == Index::PRIMARY) {
                    return 1;
                }

                if ($index1_type == Index::UNIQUE) {
                    return -1;
                }

                // If we reach here, it means: $index1=INDEX && $index2=UNIQUE
                return 1;
            });
        }

        return $matched;
    }

    public function diff(DoctrineTable $compareTable)
    {
        $connection = $this->getConnection()->createSchemaManager();
        $platform = $connection->getDatabasePlatform();
        $comparator = new Comparator( $platform);
        return $comparator->compareTables($this, $compareTable);

        // return (new Comparator())->diffTable($this, $compareTable);
    }

    public function diffOriginal()
    {
        $connection = $this->getConnection()->createSchemaManager();
        $platform = $connection->getDatabasePlatform();
        $comparator = new Comparator( $platform);
        $originalTable = SchemaManager::getDoctrineTable($this->getName());
        return $comparator->compareTables($originalTable, $this);
        // return (new Comparator())->diffTable(SchemaManager::getDoctrineTable($this->getName()), $this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'           => $this->getName(),
            'oldName'        => $this->getName(),
            'columns'        => $this->exportColumnsToArray(),
            'indexes'        => $this->exportIndexesToArray(),
            'primaryKeyName' => $this->_primaryKeyName,
            'foreignKeys'    => $this->exportForeignKeysToArray(),
            'options'        => $this->_options,
        ];
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function exportColumnsToArray()
    {
        $exportedColumns = [];

        foreach ($this->getColumns() as $name => $column) {
            $exportedColumns[] = Column::toArray($column);
        }

        return $exportedColumns;
    }

    /**
     * @return array
     */
    public function exportIndexesToArray()
    {
        $exportedIndexes = [];

        foreach ($this->getIndexes() as $name => $index) {
            $indexArr = Index::toArray($index);
            $indexArr['table'] = $this->getName();
            $exportedIndexes[] = $indexArr;
        }

        return $exportedIndexes;
    }

    /**
     * @return array
     */
    public function exportForeignKeysToArray()
    {
        $exportedForeignKeys = [];

        foreach ($this->getForeignKeys() as $name => $fk) {
            $exportedForeignKeys[$name] = ForeignKey::toArray($fk);
        }

        return $exportedForeignKeys;
    }

    public function __get($property)
    {
        $getter = 'get'.ucfirst($property);

        if (!method_exists($this, $getter)) {
            throw new \Exception("Property {$property} doesn't exist or is unavailable");
        }

        return $this->$getter();
    }
}
