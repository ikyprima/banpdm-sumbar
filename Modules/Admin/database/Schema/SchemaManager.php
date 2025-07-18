<?php

namespace Modules\Admin\database\Schema;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table as DoctrineTable;
use Illuminate\Support\Facades\DB;
use Modules\Admin\database\Types\Type;
use InvalidArgumentException;
use Doctrine\DBAL\DriverManager;
abstract class SchemaManager
{
    // todo: trim parameters
    private static $conn;

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
    public static function __callStatic($method, $args)
    {
        return static::manager()->$method(...$args);
    }
    public static function getDatabasePlatform()
    {
        $conn = self::getConnection();
            $platform = $conn->getDatabasePlatform();
        return  $platform;
    }
    public static function manager()
    {
        return self::getConnection()->createSchemaManager();
        // return DB::connection()->getDoctrineSchemaManager();
    }

    public static function getDatabaseConnection()
    {
        return self::getConnection()->createSchemaManager();
    }

    public static function tableExists($table)
    {
        if (!is_array($table)) {
            $table = [$table];
        }

        return static::manager()->tablesExist($table);
    }

    public static function listTables()
    {
        $tables = [];

        foreach (static::manager()->listTableNames() as $tableName) {
            $tables[$tableName] = static::listTableDetails($tableName);
        }

        return $tables;
    }

    /**
     * @param string $tableName
     *
     * @return \Modules\Admin\database\Schema\Table
     */
    // public static function listTableDetails($tableName)
    // {
    //     $columns = static::manager()->listTableColumns($tableName);

    //     $foreignKeys = [];

    //     $conn = self::getConnection();
    //     $platform = $conn->getDatabasePlatform();
    //     $platform = $platform->supportsForeignKeyConstraints();
        

    //     if ($platform) {
    //         $foreignKeys = static::manager()->listTableForeignKeys($tableName);
    //     }

    //     $indexes = static::manager()->listTableIndexes($tableName);
    //     return new Table($tableName, $columns, $indexes, [], $foreignKeys, []);
    // }
    public static function listTableDetails($tableName)
    {
        $columns = static::manager()->listTableColumns($tableName);

        $foreignKeys = [];

        $conn = self::getConnection();
        $platform = $conn->getDatabasePlatform();

        // DBAL 4: supportsForeignKeyConstraints() sudah dihapus,
        // kita cek manual berdasar class platform
        $supportsForeignKeyConstraints = !($platform instanceof \Doctrine\DBAL\Platforms\SQLitePlatform);

        if ($supportsForeignKeyConstraints) {
            $foreignKeys = static::manager()->listTableForeignKeys($tableName);
        }

        $indexes = static::manager()->listTableIndexes($tableName);

        return new Table($tableName, $columns, $indexes, [], $foreignKeys, []);
    }

    /**
     * Describes given table.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Support\Collection
     */
    public static function describeTable($tableName)
    {
        Type::registerCustomPlatformTypes();
        $table = static::listTableDetails($tableName);
        return collect($table->columns)->map(function ($column) use ($table) {
            $columnArr = Column::toArray($column);

            $columnArr['field'] = $columnArr['name'];
            $columnArr['type'] = $columnArr['type']['name'];

            // Set the indexes and key
            $columnArr['indexes'] = [];
            $columnArr['key'] = null;
            if ($columnArr['indexes'] = $table->getColumnsIndexes($columnArr['name'], true)) {
                // Convert indexes to Array
                foreach ($columnArr['indexes'] as $name => $index) {
                    $columnArr['indexes'][$name] = Index::toArray($index);
                }

                // If there are multiple indexes for the column
                // the Key will be one with highest priority
                $indexType = array_values($columnArr['indexes'])[0]['type'];
                $columnArr['key'] = substr($indexType, 0, 3);
            }
            
            $columnArr['foreign'] = null;
            foreach ($table->getForeignKeys() as $fk) {
                if (in_array($columnArr['name'], $fk->getLocalColumns())) {
                    $columnArr['foreign'] = [
                        'name'           => $fk->getName(),
                        'foreignTable'   => $fk->getForeignTableName(),
                        'foreignColumns' => $fk->getForeignColumns(),
                        'options'        => $fk->getOptions(),
                    ];
                    break; // kalau sudah ketemu, cukup satu FK
                }
            }

            return $columnArr;
        });
    }

    public static function listTableColumnNames($tableName)
    {
        Type::registerCustomPlatformTypes();

        $columnNames = [];

        foreach (static::manager()->listTableColumns($tableName) as $column) {
            $columnNames[] = $column->getName();
        }

        return $columnNames;
    }

    public static function createTable($table)
    {
        if (!($table instanceof DoctrineTable)) {
            $table = Table::make($table);
        }

        static::manager()->createTable($table);
    }

    public static function getDoctrineTable($table)
    {
        $table = trim($table);

        if (!static::tableExists($table)) {
            // throw SchemaException::tableDoesNotExist($table);
            throw new InvalidArgumentException("Table [{$table}] does not exist.");
        }

        return static::manager()->listTableDetails($table);
    }

    public static function getDoctrineColumn($table, $column)
    {
        return static::getDoctrineTable($table)->getColumn($column);
    }
}
