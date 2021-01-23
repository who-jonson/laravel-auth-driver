<?php


namespace WhoJonson\LaravelAuth\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as BaseUserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use WhoJonson\LaravelAuth\Support\Builder;
use WhoJonson\LaravelAuth\Abstracts\Model;

/**
 * Class UserProvider
 * @package WhoJonson\LaravelFileAuth\Providers
 */
abstract class UserProvider implements BaseUserProvider
{
    /**
     * The user model.
     *
     * @var string
     */
    protected $model;

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

    /**
     * Create a new instance of the model.
     *
     * @return Authenticatable|Model
     */
    public function createModel(): Model
    {
        $class = $this->getModelClass();

        return new $class;
    }

    /**
     * @param Authenticatable|Model|null $model
     * @return mixed|Builder
     */
    protected function newModelQuery($model = null): Builder
    {
        return is_null($model)
            ? $this->createModel()->newQuery()
            : $model->newQuery();
    }

    /**
     * Gets the hasher implementation.
     *
     * @return Hasher
     */
    public function getHasher(): Hasher
    {
        return $this->hash;
    }

    /**
     * Sets the hasher implementation.
     *
     * @param Hasher $hash
     * @return $this
     */
    public function setHasher(Hasher $hash): UserProvider
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Gets the name of the Eloquent user model.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return '\\'.ltrim($this->config['model'], '\\');
    }
}
