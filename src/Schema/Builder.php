<?php

namespace Grimzy\LaravelMysqlSpatial\Schema;

use Closure;
use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Database\Schema\MySqlBuilder;
use ReflectionMethod;

class Builder extends MySqlBuilder
{
    /**
     * Create a new command set with a Closure.
     *
     * @param string  $table
     * @param Closure|null $callback
     *
     * @return Blueprint
     */
    protected function createBlueprint($table, ?Closure $callback = null)
    {
        if ($this->blueprintTakesConnection()) {
            return new Blueprint($this->connection, $table, $callback);
        }

        $prefix = $this->connection->getConfig('prefix_indexes')
            ? $this->connection->getConfig('prefix')
            : '';

        return new Blueprint($table, $callback, $prefix);
    }

    private function blueprintTakesConnection(): bool
    {
        $parameters = (new ReflectionMethod(IlluminateBlueprint::class, '__construct'))->getParameters();

        return isset($parameters[0]) && $parameters[0]->getName() === 'connection';
    }
}
