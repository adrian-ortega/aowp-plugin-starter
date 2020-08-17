<?php

namespace AOD\Plugin\Models;

use AOD\Plugin\Database\Eloquent\Model;

abstract class AbstractEloquentPostTypeModel extends Model
{
    protected $table = 'wp_posts';

    protected $primaryKey = 'ID';

    protected $post_type = null;

    const CREATED_AT = 'post_date';

    const UPDATED_AT = 'post_modified';

    /**
     * Filter by post type
     *
     * @param $query
     * @param string $type
     *
     * @return mixed
     */
    public function scopeType($query, $type = 'post')
    {
        return $query->where('post_type', '=', $type);
    }

    /**
     * Filter by post status
     *
     * @param $query
     * @param string $status
     *
     * @return mixed
     */
    public function scopeStatus($query, $status = 'publish')
    {
        return $query->where('post_status', '=', $status);
    }

    /**
     * Filter by post author
     *
     * @param $query
     * @param null $author
     *
     * @return mixed
     */
    public function scopeAuthor($query, $author = null)
    {
        if ($author) {
            return $query->where('post_author', '=', $author);
        }
    }
}
