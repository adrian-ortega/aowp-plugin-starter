<?php

namespace AOD\Plugin\Support\Traits\Database;

use Exception;

trait HasTableNamesTrait
{
    /**
     * Returns the name of the table and adds it to the wordpress \wpdb object for easy referencing.
     *
     * @param null $table_name
     *
     * @return mixed|string
     * @throws Exception
     */
    public function getTableName($table_name = null)
    {
        if($table_name === null) {
            $table_name = $this->table_name;
        }

        $id = "aod_{$table_name}";

        if(!($connection = $this->getConnection())) {
            throw new Exception('Missing connection');
        }

        if ( ! $connection->hasCacheTableName($id) ) {
            $prefixed_table_name = $connection->getPrefix() . $id;
            $connection->cacheTableName( $id,  $prefixed_table_name);
        }

        return $connection->getCachedTableName($id);
    }
}
