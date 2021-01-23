<?php


namespace WhoJonson\LaravelAuth\Providers;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use WhoJonson\LaravelAuth\Exceptions\LaravelAuthException;
use WhoJonson\LaravelAuth\Contracts\Model;

class FileUserProvider extends UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     * @return Authenticatable|Model|null
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $class = $this->getModelClass();

        return $class::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param mixed $identifier
     * @param string $token
     * @return Authenticatable|Model|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->retrieveById($identifier);

        if (!$model) {
            return null;
        }

        return hash_equals($model->getRememberToken(), $token) ? $model : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param Authenticatable|Model $user
     * @param string $token
     * @return void
     *
     * @throws LaravelAuthException
     */
    public function updateRememberToken($user, $token) {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return Authenticatable|Model|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                Str::contains(array_key_first($credentials), 'password'))) {
            return null;
        }
        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query = $query->whereIn($key, $value);
            } else {
                $query = $query->where($key, '=', $value);
            }
        }

        return $query->values()->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param Authenticatable $user
     * @param  array  $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'];

        return $this->hash->check($plain, $user->getAuthPassword());
    }
}
