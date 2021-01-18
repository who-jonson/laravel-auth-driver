<?php


namespace WhoJonson\LaravelAuth\Providers;

use Illuminate\Contracts\Auth\UserProvider as BaseUserProvider;
use Illuminate\Contracts\Hashing\Hasher;

/**
 * Class UserProvider
 * @package WhoJonson\LaravelFileAuth\Providers
 */
abstract class UserProvider implements BaseUserProvider
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Hasher
     */
    protected $hash;

    /**
     * UserProvider constructor.
     *
     * @param Hasher $hash
     * @param array $config
     */
    public function __construct(Hasher $hash, array $config)
    {
        $this->hash = $hash;
        $this->config = $config;
    }
}
