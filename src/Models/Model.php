<?php


namespace WhoJonson\LaravelAuth\Models;

use Carbon\Carbon;
use Exception;
use BadMethodCallException;
use Illuminate\Filesystem\Filesystem;
use WhoJonson\LaravelAuth\Contracts\Model as AuthModel;
use WhoJonson\LaravelAuth\Exceptions\DuplicateUniqueException;
use WhoJonson\LaravelAuth\Exceptions\FileSystemException;
use WhoJonson\LaravelAuth\Exceptions\LaravelAuthException;
use WhoJonson\LaravelAuth\Exceptions\NotNullViolationException;
use WhoJonson\LaravelAuth\Exceptions\UndefinedFileNameException;
use WhoJonson\LaravelAuth\Traits\QueryBuilder;

/**
 * Class Model
 * @package WhoJonson\LaravelAuth\Models
 */
abstract class Model implements AuthModel
{
    use QueryBuilder;

    /**
     * The user model.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * File name where data are being stored
     *
     * @var string
     */
    protected $fileName;

    /**
     * All of the model's keys|column.
     *
     * @var string[]
     */
    protected $keys = ['id', 'name', 'email', 'password', 'remember_token', 'email_verified_at', 'created_at', 'updated_at'];

    /**
     * Model's hidden attributes.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * All of the model's attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * All of the model's attributes from store.
     *
     * @var array
     */
    protected $originals;

    /**
     * The unique identifier of the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Type of the primary key
     *
     * @var string
     */
    protected $primaryKeyType = 'int';

    /**
     * Indicates if the primary key auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * All unique keys of the model except the primary key
     *
     * @var string[]
     */
    protected $uniqueKeys = ['email'];

    /**
     * All mandatory fields/keys of the model except the primary key
     *
     * @var string[]
     */
    protected $mandatory = ['name', 'email'];

    /**
     * The column name of the "remember me" token.
     *
     * @var string
     */
    protected $rememberTokenName = 'remember_token';

    /**
     * The key name of the "password"
     *
     * @var string
     */
    protected $passwordKey = 'password';

    /**
     * Create a new Model object.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }

    /**
     * @param Filesystem $files
     * @return Model
     */
    public function setFiles(Filesystem $files): Model
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function create(array $data): Model
    {
        $model = static::instance()->setAttributes($data);
        $model->storeModel();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public static function delete($id): bool
    {
        return false;
    }

    /**
     * Dynamically access the model's attributes.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set an attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamically check if a value is set on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset a value on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset(string $key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return bool
     * @throws LaravelAuthException
     */
    public function __call($method, $arguments) : bool
    {
        if(method_exists($this, $method)) {
            $this->checkForFileName();
            return call_user_func_array([$this, $method], $arguments);
        } else {
            throw new BadMethodCallException();
        }
    }

    /**
     * @throws LaravelAuthException
     */
    protected function checkForFileName() {
        if(!$this->fileName) {
            throw new UndefinedFileNameException();
        }
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $key, $value) {
        if(!in_array($key, $this->keys)) {
            return;
        }
        if($key == $this->primaryKey) {
            $this->setPrimaryKey($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed|void
     */
    public function getAttribute(string $key) {
        if(!$key || !in_array($key, $this->keys)) {
            return;
        }
        if (method_exists(self::class, $key)) {
            return;
        }

        return array_key_exists($key, $this->attributes)
            ? $this->attributes[$key]
            : null;
    }

    /**
     * @return string
     */
    protected static function getFilePath(): string
    {
        $dir = (string) (config('auth-driver.file.directory') ?? storage_path('app/auth-db'));

        return get_absolute_path($dir . '/' . (new static)->fileName . '.txt');
    }

    /**
     * @return array
     */
    protected static function getData(): array
    {
        $file = static::getFilePath();
        if(!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        if($data && strlen($data) > 0) {
            return json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
        }
        return [];
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newInstance($attributes = []): Model
    {
        return new static((array) $attributes);
    }

    /**
     * new static instance the model.
     */
    public static function instance(): Model
    {
        return (new static)->newInstance();
    }

    /**
     * Begin querying the model.
     *
     * @return Builder
     */
    public static function query(): Builder
    {
        return (new static)->newQuery();
    }

    /**
     * Get a new query builder for the model's data.
     *
     * @return Builder
     */
    public function newQuery(): Builder
    {
        return new Builder(static::instance(), static::getData());
    }

    /**
     * @return array
     */
    public function getUniqueKeys() : array
    {
        return array_merge($this->uniqueKeys, [$this->primaryKey]);
    }

    /**
     * @return array
     */
    public function getMandatoryKeys() : array
    {
        return array_merge($this->mandatory, [$this->primaryKey]);
    }

    /**
     * Set the array of model attributes
     *
     * @param  array  $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set the array of model attributes(original from store)
     *
     * @param  array  $attributes
     * @return $this
     */
    public function setOriginals(array $attributes): Model
    {
        foreach ($attributes as $key => $value) {
            if(in_array($key, $this->keys)) {
                $this->originals[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|int|null $ignore
     *
     * @return bool
     */
    protected function checkIfUniqueValue(string $key, $value, $ignore = null): bool
    {
        $data = $this->newQuery();
        if($data->count() > 0) {
            if(!$ignore) {
                return !$data->contains($key, '=', $value);
            }

            $idKey = $this->primaryKey;
            return !$data->contains(function ($val) use ($key, $value, $ignore, $idKey) {
                return $val[$key] == $value && $val[$idKey] != $ignore;
            });
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        try {
            if(isset($this->originals[$this->primaryKey]) && $this->originals[$this->primaryKey]) {
                $this->storeModel($this->originals[$this->primaryKey]);
            } else {
                if(!$this->getAttribute($this->primaryKey)) {
                    $this->setPrimaryKey();
                }
                $this->storeModel();
            }
            return true;
        } catch (LaravelAuthException $e) {
            throw $e;
        }
    }

    /**
     *
     * @param string|int|null $id
     * @throws LaravelAuthException
     */
    protected function storeModel($id = null) {
        $uniqueKeys = $this->getUniqueKeys();

        foreach ($this->attributes as $key => $value) {
            // Check if null value for a mandatory field
            if(!$value && in_array($key, $this->getMandatoryKeys())) {
                throw new NotNullViolationException($key);
            }
            // Check for unique field
            if(in_array($key, $uniqueKeys) && !$this->checkIfUniqueValue($key, $value, $id)) {
                throw new DuplicateUniqueException($key);
            }

            if($key =='created_at' && !$value) {
                $this->setAttribute($key, Carbon::now()->toDateTimeString());
            }
            if($key =='updated_at') {
                $this->setAttribute($key, Carbon::now()->toDateTimeString());
            }
        }
        static::updateFile($this->attributes);
    }

    /**
     * @param array $data
     *
     * @throws LaravelAuthException
     */
    protected static function storeData(array $data) {
        try {
            $fp = fopen(static::getFilePath(), 'wb');
            fwrite($fp, json_encode($data));
            fclose($fp);
            // file_put_contents(static::getFilePath(), json_encode($data));
        } catch (Exception $exception) {
            throw new FileSystemException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param string|int|null
     */
    protected function setPrimaryKey($id = null) {
        if ($this->primaryKeyType == 'int' && $this->incrementing) {
            $id = ((int) $this->newQuery()->max($this->primaryKey)) + 1;
        }
        $this->attributes[$this->primaryKey] = $id;
    }

    /**
     * @param array $attributes
     * @throws LaravelAuthException
     */
    protected static function updateFile(array $attributes) {
        $data = static::getData();
        $primaryKey = (new static)->primaryKey;

        $exists = array_filter($data, function ($item) use ($primaryKey, $attributes) {
            return $item[$primaryKey] == $attributes[$primaryKey];
        });
        if(count($exists) > 0) {
            $index = array_key_first($exists);
            foreach ($attributes as $k => $v) {
                if($k != $primaryKey) {
                    $data[$index][$k] = $v;
                }
            }
        } else {
            array_push($data, $attributes);
        }
        static::storeData($data);
    }
}
