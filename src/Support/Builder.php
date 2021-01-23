<?php


namespace WhoJonson\LaravelAuth\Support;

use Illuminate\Support\Collection;
use WhoJonson\LaravelAuth\Abstracts\Model;

class Builder extends Collection
{

    /**
     * @var string
     */
    public $model;

    /**
     * Create a new Builder.
     *
     * @param string $model
     * @param mixed $items
     */
    public function __construct(string $model, $items = [])
    {
        parent::__construct($items);
        $this->model = $model;
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @param array $items
     * @return Builder
     */
    public function setItems(array $items = []): Builder
    {
        $this->items = $this->getArrayableItems($items);
        return $this;
    }

    /**
     * @param callable|null $callback
     * @param null $default
     * @return Model|mixed|null
     */
    public function first(callable $callback = null, $default = null)
    {
        $data = parent::first($callback, $default);
        if(!$data) {
            return null;
        }
        return $this->convertToModel($data);
    }

    /**
     * @param string $key
     * @param null $operator
     * @param mixed|null $value
     *
     * @return Model|null
     */
    public function firstWhere($key, $operator = null, $value = null): ?Model
    {
        $data = parent::firstWhere($key, $operator, $value);
        if(!$data) {
            return null;
        }
        return $this->convertToModel($data);
    }

    /**
     * @param mixed|array $key
     * @param null $default
     *
     * @return Model[]|Collection|null
     */
    public function get($key = [], $default = null)
    {
        if($this->count() <= 0) {
            return null;
        }

        $models = new Collection();
        foreach ($this->items as $value) {
            $models->push($this->convertToModel($value));
        }
        return $models;
    }

    /**
     * @param array|object $data
     * @return Model
     */
    protected function convertToModel($data): Model
    {
        return new $this->model((array) $data);
    }
}
