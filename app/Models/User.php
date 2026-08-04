<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * System user / staff account.
 *
 * Passwords may be stored as either a Laravel bcrypt hash OR plain text (legacy
 * behaviour preserved from the original JavaFX app). The Authenticator and
 * AuthController handle both forms transparently — when a plain-text password
 * is verified it is transparently re-hashed and saved.
 *
 * @property string $user_id
 * @property string $first_name
 * @property string|null $last_name  (middle name in the original app)
 * @property string|null $phone_number
 * @property string|null $email_id
 * @property string $job_role
 * @property string $user_name
 * @property string $user_password
 * @property string|null $photo  (binary BLOB)
 * @property \Carbon\Carbon $created_at
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_account';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'phone_number', 'email_id',
        'job_role', 'user_name', 'user_password', 'photo', 'remember_token',
    ];

    /**
     * Override the password field name — this app uses `user_password`
     * rather than the default `password`.
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Unique identifier column name for authentication.
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function meterLocations()
    {
        return $this->hasMany(MeterLocation::class, 'customer_code', 'user_id');
    }

    /**
     * Determine if the stored password is already hashed (bcrypt/argon2).
     */
    public function passwordIsHashed(): bool
    {
        $info = password_get_info($this->user_password);

        return $info['algo'] !== null && $info['algo'] !== 0;
    }
}
