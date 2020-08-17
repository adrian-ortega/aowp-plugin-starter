<?php

namespace AOD\Plugin\Support\Traits\Database;

use AOD\Plugin\Database\Eloquent\Database;

trait HasConnectionTrait
{
    /**
     * @return false|Database
     */
    protected function getConnection()
    {
        return property_exists($this, 'db') ? $this->db : Database::getInstance();
    }

    /**
     * @param $table_name
     *
     * @return bool
     */
    private function tableExists( $table_name )
    {
        return $this->getConnection()->getSchemaBuilder()->hasTable( $table_name );
    }
}
