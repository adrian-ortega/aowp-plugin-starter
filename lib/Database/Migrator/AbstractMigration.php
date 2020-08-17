<?php

namespace AOD\Plugin\Database\Migrator;

use AOD\Plugin\Database\Eloquent\Database;
use AOD\Plugin\Support\Traits\Database\HasConnectionTrait;
use AOD\Plugin\Support\Traits\Database\HasTableNamesTrait;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

abstract class AbstractMigration
{
    use HasTableNamesTrait, HasConnectionTrait;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var SchemaBuilder
     */
    protected $schema;

    /**
     * Enables, if supported, wrapping the migration within a transaction.
     *
     * @var bool
     */
    public $withinTransaction = true;

    public function __construct(Database $db, SchemaBuilder $schema)
    {
        $this->db = $db;
        $this->schema = $schema;
    }

    abstract public function up();

    abstract public function down();

    /**
     * @return SchemaBuilder
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
