<?php

namespace AOD\Plugin\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class User
 * @package AOD\Plugin\Models
 *
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property Date $user_registered
 * @property string $user_activation_key
 * @property int $user_status
 * @property string $display_name
 */
class User extends AbstractEloquentModel
{
    protected $primaryKey = 'ID';

    public $timestamps = false;

    /**
     * @return HasMany
     */
    public function meta()
    {
        return $this->hasMany( UserMeta::class, 'user_id' );
    }
}
