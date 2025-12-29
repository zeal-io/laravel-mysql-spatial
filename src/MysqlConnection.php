<?php

namespace Grimzy\LaravelMysqlSpatial;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\DBAL\DriverManager;
use Grimzy\LaravelMysqlSpatial\Schema\Builder;
use Grimzy\LaravelMysqlSpatial\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\MySqlConnection as IlluminateMySqlConnection;

class MysqlConnection extends IlluminateMySqlConnection
{
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        if (class_exists(DoctrineType::class)) {
            // Prevent geometry type fields from throwing a 'type not found' error when changing them
            $geometries = [
                'geometry',
                'point',
                'linestring',
                'polygon',
                'multipoint',
                'multilinestring',
                'multipolygon',
                'geometrycollection',
                'geomcollection',
            ];
            
            try {
                // Laravel 11: getDoctrineSchemaManager() was removed, use Doctrine DBAL directly
                if (method_exists($this, 'getDoctrineSchemaManager')) {
                    // Laravel 10 and below
                    $dbPlatform = $this->getDoctrineSchemaManager()->getDatabasePlatform();
                } else {
                    // Laravel 11+: Create Doctrine connection from PDO
                    $doctrineConnection = DriverManager::getConnection([
                        'pdo' => $this->getPdo(),
                    ]);
                    $dbPlatform = $doctrineConnection->getDatabasePlatform();
                }
                
                foreach ($geometries as $type) {
                    $dbPlatform->registerDoctrineTypeMapping($type, 'string');
                }
            } catch (\Exception $e) {
                // Silently fail if Doctrine is not properly configured
                // Spatial functionality will still work, but column type changes may need manual handling
            }
        }
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return new MySqlGrammar($this);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }
}
