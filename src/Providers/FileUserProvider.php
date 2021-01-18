<?php


namespace WhoJonson\LaravelAuth\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use WhoJonson\LaravelAuth\Traits\FileUser;

class FileUserProvider extends UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return FileUser::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {

    }

    /**
     * @inheritDoc
     */
    public function updateRememberToken(Authenticatable $user, $token) {

    }

    /**
     * @inheritDoc
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $builder = FileUser::query();
        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $builder = $builder->where($key, '=', $value);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {

    }
}
