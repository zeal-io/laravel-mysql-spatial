<?php

use Grimzy\LaravelMysqlSpatial\MysqlConnection;
use Grimzy\LaravelMysqlSpatial\Schema\Builder;
use PHPUnit\Framework\TestCase;
use Stubs\PDOStub;

class MysqlConnectionTest extends TestCase
{
    private $mysqlConnection;

    protected function setUp(): void
    {
        $mysqlConfig = ['driver' => 'mysql', 'prefix' => 'prefix', 'database' => 'database', 'name' => 'foo'];
        $this->mysqlConnection = new MysqlConnection(new PDOStub(), 'database', 'prefix', $mysqlConfig);
    }

    public function testGetSchemaBuilder()
    {
        $builder = $this->mysqlConnection->getSchemaBuilder();

        $this->assertInstanceOf(Builder::class, $builder);
    }

    public function testConstructorDoesNotResolveThePdo()
    {
        $resolved = 0;

        new MysqlConnection(function () use (&$resolved) {
            $resolved++;

            return new PDOStub();
        }, 'database', 'prefix', ['driver' => 'mysql', 'prefix' => 'prefix', 'database' => 'database', 'name' => 'foo']);

        $this->assertSame(0, $resolved);
    }
}
