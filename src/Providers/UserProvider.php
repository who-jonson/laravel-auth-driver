<?php


namespace WhoJonson\LaravelAuth\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as BaseUserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Filesystem\Filesystem;
use WhoJonson\LaravelAuth\Models\Builder;
use WhoJonson\LaravelAuth\Models\Model;

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
     * The user model.
     *
     * @var Filesystem
     */
    protected $files;

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
     * @param Filesystem $files
     * @param Hasher $hash
     * @param array $config
     */
    public function __construct(Filesystem $files, Hasher $hash, array $config)
    {
        $this->files = $files;
        $this->hash = $hash;
        $this->config = $config;

        $this->setModel($this->config['model']);
    }

    /**
     * Create a new instance of the model.
     *
     * @return Authenticatable|Model
     */
    public function createModel(): Model
    {
        $class = '\\'.ltrim($this->model, '\\');

        return (new $class)->setFiles($this->getFiles());
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
    public function getModel(): string
    {
        return '\\'.ltrim($this->model, '\\');
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): UserProvider
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }
}
