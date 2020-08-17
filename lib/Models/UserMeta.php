<?php

namespace AOD\Plugin\Models;

/**
 * Class UserMeta
 * @package AOD\Plugin\Models
 * @property string $meta_key
 * @property string $meta_value
 * @property int $user_id
 */
class UserMeta extends AbstractEloquentModel
{
    protected $primaryKey = 'umeta_id';

    public $timestamps = false;

    public function getTable()
    {
        return $this->getConnection()->db->usermeta;
    }
}
