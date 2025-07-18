<?php
namespace Modules\Admin\database;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Comparator;

use Doctrine\DBAL\Schema\TableDiff;
use Modules\Admin\database\Schema\SchemaManager;
use Modules\Admin\database\Schema\Table;
use Modules\Admin\database\Types\Type;
use Doctrine\DBAL\DriverManager;

class DatabaseUpdater
{
    protected $tableArr;
    protected $table;
    protected $originalTable;
    private static $conn;

    public function __construct(array $tableArr)
    {
        Type::registerCustomPlatformTypes();

        $this->table = Table::make($tableArr);
        $this->tableArr = $tableArr;
        $this->originalTable = SchemaManager::listTableDetails($tableArr['oldName']);
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

    /**
     * Update the table.
     *
     * @return void
     */
    public static function update($table)
    {
        if (!is_array($table)) {
            $table = json_decode($table, true);
        }

        if (!SchemaManager::tableExists($table['oldName'])) {
            // throw SchemaException::tableDoesNotExist($table['oldName']);
            throw new InvalidArgumentException("Table [{$table['oldName']}] does not exist.");
        }

        $updater = new self($table);

        $updater->updateTable();
    }

    /**
     * Updates the table.
     *
     * @return void
     */
    public function updateTable()
    {
        // Get table new name
        if (($newName = $this->table->getName()) != $this->originalTable->getName()) {
            // Make sure the new name doesn't already exist
            if (SchemaManager::tableExists($newName)) {
                // throw SchemaException::tableAlreadyExists($newName);
                throw new InvalidArgumentException("Table [{$newName}] already exists.");

            }
        } else {
            $newName = false;
        }

        // Rename columns
        if ($renamedColumnsDiff = $this->getRenamedColumnsDiff()) {
            SchemaManager::alterTable($renamedColumnsDiff);

            // Refresh original table after renaming the columns
            $this->originalTable = SchemaManager::listTableDetails($this->tableArr['oldName']);
        }

        $tableDiff = $this->originalTable->diff($this->table);

        // Add new table name to tableDiff
        if ($newName) {
            if (!$tableDiff) {
                $tableDiff = new TableDiff($this->tableArr['oldName']);
                $tableDiff->fromTable = $this->originalTable;
            }

            $tableDiff->newName = $newName;
        }

        // Update the table
        if ($tableDiff) {
            SchemaManager::alterTable($tableDiff);
        }
    }

    /**
     * Get the table diff to rename columns.
     *
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    // protected function getRenamedColumnsDiff()
    // {
        
    //     $renamedColumns = $this->getRenamedColumns();

    //     if (empty($renamedColumns)) {
    //         return false;
    //     }

    //     $renamedColumnsDiff = new TableDiff($this->tableArr['oldName']);
    //     $renamedColumnsDiff->fromTable = $this->originalTable;


    //     foreach ($renamedColumns as $oldName => $newName) {
    //         $renamedColumnsDiff->renamedColumns[$oldName] = $this->table->getColumn($newName);

    //     }

    //     return $renamedColumnsDiff;
    // }

    protected function getRenamedColumnsDiff(): array|bool
    {
        // $connection = \DB::connection()->getDoctrineConnection();
        $connection = $this->getConnection()->createSchemaManager();
        $platform = $connection->getDatabasePlatform();
        $comparator = new Comparator( $platform);

        $tableDiff = $comparator->compareTables($this->originalTable, $this->table);

        if ($tableDiff->isEmpty()) {
            return false;
        }

        $renamed = [];

        foreach ($tableDiff->getChangedColumns() as $diff) {
            if ($diff->hasNameChanged()) {
                $oldName = $diff->getOldColumn()->getName();
                $newName = $diff->getNewColumn()->getName();
                $renamed[$oldName] = $newName;
            }
        }

        return $renamed;
    }

    /**
     * Get the table diff to rename columns and indexes.
     *
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    // protected function getRenamedDiff()
    // {
    //     $renamedColumns = $this->getRenamedColumns();
    //     $renamedIndexes = $this->getRenamedIndexes();

    //     if (empty($renamedColumns) && empty($renamedIndexes)) {
    //         return false;
    //     }

    //     $renamedDiff = new TableDiff($this->tableArr['oldName']);
    //     $renamedDiff->fromTable = $this->originalTable;

    //     foreach ($renamedColumns as $oldName => $newName) {
    //         $renamedDiff->renamedColumns[$oldName] = $this->table->getColumn($newName);
           
    //     }

    //     foreach ($renamedIndexes as $oldName => $newName) {
    //         $renamedDiff->renamedIndexes[$oldName] = $this->table->getIndex($newName);
        
    //     }

    //     return $renamedDiff;
    // }

    /**
     * Get columns that were renamed.
     *
     * @return array
     */
    protected function getRenamedColumns()
    {
        $renamedColumns = [];

        foreach ($this->tableArr['columns'] as $column) {
            $oldName = $column['oldName'];

            // make sure this is an existing column and not a new one
            if ($this->originalTable->hasColumn($oldName)) {
                $name = $column['name'];

                if ($name != $oldName) {
                    $renamedColumns[$oldName] = $name;
                }
            }
        }

        return $renamedColumns;
    }

    /**
     * Get indexes that were renamed.
     *
     * @return array
     */
    protected function getRenamedIndexes()
    {
        $renamedIndexes = [];

        foreach ($this->tableArr['indexes'] as $index) {
            $oldName = $index['oldName'];

            // make sure this is an existing index and not a new one
            if ($this->originalTable->hasIndex($oldName)) {
                $name = $index['name'];

                if ($name != $oldName) {
                    $renamedIndexes[$oldName] = $name;
                }
            }
        }

        return $renamedIndexes;
    }
}
