<?php

namespace AOD\Plugin\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends AbstractEloquentPostTypeModel
{
    protected $post_type = 'post';

    /**
     * @return HasMany
     */
    public function meta()
    {
        return $this->hasMany( PostMeta::class, 'post_id');
    }
}
