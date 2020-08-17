<?php

namespace AOD\Plugin\Database\Eloquent;

use Closure;
use Exception;
use Generator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Grammar;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;
use PDO;
use wpdb;

class Database extends MySqlConnection implements ConnectionInterface
{
    /**
     * @var wpdb
     */
    public $db;

    /**
     * Count of active transactions
     *
     * @var int
     */
    public $transactionCount = 0;

    /**
     * Overrides the parent instance method because it returns a self, this returns a static
     * @return false|Database
     */
    public static function getInstance()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new static(null, null, null, []);
        }

        return $instance;
    }

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        global $wpdb;
        $this->config = [
            'driver' => 'mysql',
            'name' => 'aod-eloquent-mysql'
        ];

        $this->db = $wpdb;
        parent::__construct($pdo, $wpdb->dbname, $tablePrefix, $config);
    }

    /**
     * Begin a fluent query against a database table
     *
     * @param string $table
     * @return QueryBuilder|Builder
     */
    public function table($table)
    {
        return $this->query()->from($this->withStringTablePrefix($table));
    }

    /**
     * Get a new raw query expression
     *
     * @param mixed $value
     * @return Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Get a new query builder instance
     * @return QueryBuilder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Run a select statement and return a single result
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return array|mixed|object|void|null
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bindParams($query, $bindings);
        $row = $this->db->get_row($query);

        if ($row === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new Exception($this->db->last_error));
        }

        return $row;
    }

    /**
     * Run a select statement against the database
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return array|void
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bindParams($query, $bindings);
        $results = $this->db->get_results($query);

        if ($results === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new Exception($this->db->last_error));
        }

        return $results;
    }

    /**
     * Run a select statement against the databse and return a generator
     * @TODO implement
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return Generator|null
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        return null;
    }

    /**
     * Run an insert statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->unprepared($this->bindParams($query, $bindings));
    }

    /**
     * Run an SQL statement and get the number of the rows affected
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        $query = $this->bindParams($query, $bindings);
        $result = $this->db->query($query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new Exception($this->db->last_error));
        }

        return intval($result);
    }

    /**
     * Run a raw, unprepared query against the PDO connection
     * @param string $query
     * @return bool
     */
    public function unprepared($query)
    {
        $result = $this->db->query($query);
        return ($result === false || $this->db->last_error);
    }

    /**
     * Execute a Closure within a transaction
     * @param Closure $callback
     * @param int $attempts
     * @return mixed
     * @throws Exception
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();
        try {
            $data = $callback();
            $this->commit();
            return $data;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Start a new database transaction
     *
     * @return void
     */
    public function beginTransaction()
    {
        $transaction = $this->unprepared("START TRANSACTION;");
        if (false !== $transaction) {
            $this->transactionCount++;
        }
    }

    /**
     * Commit the active database transaction
     * @return void
     */
    public function commit()
    {
        if ($this->transactionCount < 1) {
            return;
        }

        $transaction = $this->unprepared("COMMIT;");

        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * Rollback the active database transaction
     *
     * @param null $toLevel
     */
    public function rollBack($toLevel = null)
    {
        if ($this->transactionCount < 1) {
            return;
        }

        $transaction = $this->unprepared("ROLLBACK;");

        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactionCount;
    }

    /**
     * Return the last inserted id
     *
     * @param string $args
     * @return int
     */
    public function lastInsertId($args)
    {
        return $this->db->insert_id;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Custom methods and overrides

    /**
     * A hacky way to emulate bind parameters in SQL Query.
     * @TODO we can probably replace this with a wpdb::prepare( ... ) callback.
     * @param string $query
     * @param array|string[] $bindings
     * @return string|string[]
     */
    private function bindParams($query, $bindings = [])
    {
        $query = str_replace('"', '`', $query);

        if (!($bindings = $this->prepareBindings($bindings))) {
            return $query;
        }

        $bindings = array_map(function ($replace) {
            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }
            return $replace;
        }, $bindings);

        $query = str_replace(['%', '?'], ['%%', '%s'], $query);
        $query = vsprintf($query, $bindings);

        return $query;
    }

    /**
     * @param $table
     *
     * @return string
     */
    public function withStringTablePrefix($table)
    {
        if (strpos($table, $prefix = $this->db->prefix) !== false) {
            $table = str_replace($prefix, '', $table);
        }

        return $prefix . $table;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->db->prefix;
    }

    /**
     * Checks to see if a table name is saved within the wpdb instance
     * @param string $id
     * @return bool
     */
    public function hasCacheTableName($id)
    {
        return property_exists($this->db, $id);
    }

    /**
     * Caches the name of a table within the wpdb instance
     * @param string $id
     * @param string $table_name
     * @return $this
     */
    public function cacheTableName($id, $table_name)
    {
        $this->db->{$id} = $table_name;
        return $this;
    }

    /**
     * @param string|null $id
     *
     * @return mixed|null
     */
    public function getCachedTableName($id = null)
    {
        return $this->hasCacheTableName($id) ? $this->db->{$id} : null;
    }

    /**
     * @return Grammar|SchemaGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * @return $this|Closure|PDO
     */
    public function getPdo()
    {
        return $this;
    }
}
