<?php

namespace AOD\Plugin\Database\Eloquent;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

/**
 * Model Class
 *
 * @package WeDevs\ERP\Framework
 *
 * @method EloquentModel|Collection|null static find($id, $columns = ['*'])
 * @method EloquentModel|EloquentBuilder|null first($columns = ['*'])
 * @method EloquentModel|EloquentBuilder firstOrFail($columns = ['*'])
 * @method Collection|EloquentBuilder[] get($columns = ['*'])
 * @method mixed value($column)
 * @method mixed pluck($column)
 * @method void chunk($count, callable $callback)
 * @method \Illuminate\Support\Collection lists($column, $key = null)
 * @method LengthAwarePaginator paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
 * @method Paginator simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page')
 * @method int increment($column, $amount = 1, array $extra = [])
 * @method int decrement($column, $amount = 1, array $extra = [])
 * @method void onDelete(Closure $callback)
 * @method EloquentModel[] getModels($columns = ['*'])
 * @method array eagerLoadRelations(array $models)
 * @method array loadRelation(array $models, $name, Closure $constraints)
 * @method EloquentBuilder has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
 * @method Model create(array $attributes = [])
 * @method QueryBuilder where($column, $operator = null, $value = null)
 * @method QueryBuilder whereRaw($sql, array $bindings = [])
 * @method QueryBuilder whereBetween($column, array $values)
 * @method QueryBuilder whereNotBetween($column, array $values)
 * @method QueryBuilder whereNested(Closure $callback)
 * @method QueryBuilder addNestedWhereQuery($query)
 * @method QueryBuilder whereExists(Closure $callback)
 * @method QueryBuilder whereNotExists(Closure $callback)
 * @method QueryBuilder whereIn($column, $values)
 * @method QueryBuilder whereNotIn($column, $values)
 * @method QueryBuilder whereNull($column)
 * @method QueryBuilder whereNotNull($column)
 * @method QueryBuilder orWhere($column, $operator = null, $value = null)
 * @method QueryBuilder orWhereRaw($sql, array $bindings = [])
 * @method QueryBuilder orWhereBetween($column, array $values)
 * @method QueryBuilder orWhereNotBetween($column, array $values)
 * @method QueryBuilder orWhereExists(Closure $callback)
 * @method QueryBuilder orWhereNotExists(Closure $callback)
 * @method QueryBuilder orWhereIn($column, $values)
 * @method QueryBuilder orWhereNotIn($column, $values)
 * @method QueryBuilder orWhereNull($column)
 * @method QueryBuilder orWhereNotNull($column)
 * @method QueryBuilder whereDate($column, $operator, $value)
 * @method QueryBuilder whereDay($column, $operator, $value)
 * @method QueryBuilder whereMonth($column, $operator, $value)
 * @method QueryBuilder whereYear($column, $operator, $value)
 */
abstract class Model extends EloquentModel
{

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        static::$resolver = new Resolver();
        parent::__construct($attributes);
        $this->setValidTableName();
    }

    /**
     * This will check to make sure that every table has the valid WordPress prefix attached to it
     */
    private function setValidTableName()
    {
        if(!empty($this->table)) {
            $prefix = $this->getConnection()->db->prefix;

            if(!Str::startsWith($this->table, $prefix)) {
                $this->table = $prefix . $this->table;
            }
        }
    }

    /**
     * Get the database connection for the model.
     *
     * @return Database
     */
    public function getConnection()
    {
        return Database::getInstance();
    }

    /**
     * Get the table associated with the model.
     *
     * Append the WordPress table prefix with the table name if
     * no table name is provided
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        $table = str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));

        return $this->getConnection()->db->prefix . $table;
    }
}
