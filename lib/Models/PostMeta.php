<?php

namespace AOD\Plugin\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class PostMeta
 * @package AOD\Plugin\Models
 *
 * @property int $post_id
 * @property string $meta_key
 * @property mixed $meta_value
 */
class PostMeta extends AbstractEloquentModel
{
    protected $primaryKey = 'meta_id';

    public $timestamps = false;

    /**
     * @return HasOne
     */
    public function post()
    {
        return $this->hasOne(Post::class, 'ID');
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->getConnection()->db->postmeta;
    }
}
