<?php


namespace WhoJonson\LaravelAuth\Abstracts;


use Exception;
use Carbon\Carbon;
use BadMethodCallException;
use Illuminate\Support\Str;
use WhoJonson\LaravelAuth\Contracts\Model as ModelContract;
use WhoJonson\LaravelAuth\Exceptions\DuplicateUniqueException;
use WhoJonson\LaravelAuth\Exceptions\FileSystemException;
use WhoJonson\LaravelAuth\Exceptions\LaravelAuthException;
use WhoJonson\LaravelAuth\Exceptions\NotNullViolationException;
use WhoJonson\LaravelAuth\Support\Builder;
use WhoJonson\LaravelAuth\Traits\HasAttributes;
use WhoJonson\LaravelAuth\Traits\QueryBuilder;

/**
 * Class Model
 * @package WhoJonson\LaravelAuth\Models
 */
abstract class Model implements ModelContract
{
    use HasAttributes, QueryBuilder;

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
        $this->setUp($attributes);
    }

    /**
     * @param array $attributes
     */
    private function setUp(array $attributes) {
        foreach ($this->keys as $key) {
            if(!array_key_exists($key, $attributes)) {
                $attributes[$key] = null;
            }
        }
        $this->setOriginals($attributes)->setAttributes($attributes);
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
     * @throws BadMethodCallException
     */
    public function __call($method, $arguments) : bool
    {
        if(method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        }
        throw new BadMethodCallException();
    }

    /**
     * Get the file name for store
     *
     * @return string
     */
    protected function getFileName(): string
    {
        if(!$this->fileName) {
            return Str::snake(Str::pluralStudly(class_basename($this)));
        }
        return $this->fileName;
    }

    /**
     * @return string
     */
    protected static function getFilePath(): string
    {
        $dir = (string) (config('auth-driver.file.directory') ?? storage_path('app/db'));

        return get_absolute_path($dir . '/' . (new static)->getFileName() . '.usr');
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
     * @param array $attributes
     * @return Model
     */
    public static function instance(array $attributes = []): Model
    {
        return (new static)->newInstance($attributes);
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
        return new Builder(static::class, static::getData());
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
     * @param string $key
     * @param mixed $value
     * @param string|int|null $ignore
     *
     * @return bool
     */
    protected function checkIfUniqueValue(string $key, $value, $ignore = null): bool
    {
        $ignore = $ignore ?? $this->getAttribute($this->primaryKey);

        if($key == $this->primaryKey) {
            $data = static::findData($ignore);
        } else {
            $data = static::findDataByKeyValue($key, $value, $ignore);
        }

        return $data ? false : true;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        try {
            $this->storeModel();
            return true;
        } catch (LaravelAuthException $e) {
            throw $e;
        }
    }

    /**
     *
     * @throws LaravelAuthException
     */
    protected function storeModel() {
        $exists = static::findData($this->getOriginal($this->primaryKey));

        foreach ($this->getAttributes() as $key => $value) {
            // Check if null value for a mandatory field
            if(!$value && in_array($key, $this->getMandatoryKeys())) {
                throw new NotNullViolationException($key);
            }
            // Check for unique field
            if(in_array($key, $this->getUniqueKeys())) {
                $duplicate = false;
                if($exists) {
                    if($key == $this->primaryKey) {
                        continue;
                    }
                    if(!$this->checkIfUniqueValue($key, $value, $exists[$this->primaryKey])) {
                        $duplicate = true;
                    }
                } elseif(!$this->checkIfUniqueValue($key, $value)) {
                    $duplicate = true;
                }
                if($duplicate) {
                    throw new DuplicateUniqueException($key);
                }
            }

            if($key == 'created_at' && !$value) {
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
            file_put_contents(static::getFilePath(), json_encode($data));
        } catch (Exception $exception) {
            throw new FileSystemException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param array $attributes
     * @throws LaravelAuthException
     */
    protected static function updateFile(array $attributes) {
        $data = static::getData();
        $primaryKey = (new static)->primaryKey;

        $index = $attributes[$primaryKey];
        if(array_key_exists($index, $data)) {
            foreach ($attributes as $k => $v) {
                if($k != $primaryKey) {
                    $data[$index][$k] = $v;
                }
            }
        } else {
            $data[$index] = $attributes;
        }
        static::storeData($data);
    }

    public function delete(): bool
    {
        $data = static::getData();
        unset($data[$this->primaryKey]);
        return true;
    }

    public function toArray(): array
    {
        return (array) $this->getAttributes();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
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
            return json_decode($data, true, 512, JSON_BIGINT_AS_STRING);
        }
        return [];
    }

    /**
     * @param string|int $identifier
     *
     * @return array|null
     */
    protected static function findData($identifier) : ?array
    {
        $data = static::getData();
        if(count($data) > 0 && array_key_exists($identifier, $data)) {
            return $data[$identifier];
        }
        return null;
    }

    protected static function findDataByKeyValue(string $key, $value, $ignore = null): ?array
    {
        $data = static::getData();
        if(count($data) > 0) {
            foreach ($data as $index => $item) {
                if($ignore && $index == $ignore) {
                    continue;
                }
                if($item[$key] == $value) {
                    return $item;
                }
            }
        }

        return null;
    }
}
