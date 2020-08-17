<?php

namespace AOD\Plugin\Database\Migrator;

use AOD\Plugin\Database\Eloquent\Builder;
use AOD\Plugin\Support\Traits\Database\HasConnectionTrait;
use AOD\Plugin\Support\Traits\Database\HasTableNamesTrait;
use Exception;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;


class Repository
{
    use HasConnectionTrait, HasTableNamesTrait;

    /**
     * Checks to see if the migrations table exists or creates it
     * @throws Exception
     */
    public function checkOrCreateRepository()
    {
        if ( ! $this->tableExists($table_name = $this->getTableName('migrations'))) {
            $schema = $this->getConnection()->getSchemaBuilder();

            $schema->create($table_name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration');
                $table->float('batch');
            });
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRanMigrations()
    {
        return $this->table()
                    ->orderBy('batch', 'asc')
                    ->orderBy('migration', 'asc')
                    ->pluck('migration')
                    ->all();
    }

    /**
     * @param string $migration
     *
     * @param $batch
     *
     * @return int
     * @throws Exception
     */
    public function log($migration, $batch)
    {
        return $this->table()->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
    }

    /**
     * @return QueryBuilder|Builder
     * @throws Exception
     */
    public function table()
    {
        return $this->getConnection()->table($this->getTableName('migrations'));
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getNextBatchNumber()
    {
        return (int)$this->table()->max('batch');
    }
}
