<?php
use Modules\Admin\database\Schema\SchemaManager;

if (!function_exists('get_reflection_method')) {
    function get_reflection_method($object, $method)
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }
}

if (!function_exists('call_protected_method')) {
    function call_protected_method($object, $method, ...$args)
    {
        return get_reflection_method($object, $method)->invoke($object, ...$args);
    }
}

if (!function_exists('get_reflection_property')) {
    function get_reflection_property($object, $property)
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }
}

if (!function_exists('get_protected_property')) {
    function get_protected_property($object, $property)
    {
        return get_reflection_property($object, $property)->getValue($object);
    }
}
if (! function_exists('getDatabasePlatformName')) {
    function getDatabasePlatformName(): string
    {
        $platform = SchemaManager::getConnection()->getDatabasePlatform();

        if ($platform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform) {
            return 'mysql';
        } elseif ($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            return 'pgsql';
        } elseif ($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            return 'sqlite';
        } elseif ($platform instanceof \Doctrine\DBAL\Platforms\SQLServerPlatform) {
            return 'sqlsrv';
        }

        return 'unknown';
    }
}
