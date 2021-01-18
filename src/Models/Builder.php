<?php


namespace WhoJonson\LaravelAuth\Models;


use Illuminate\Support\Collection;

class Builder extends Collection
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * Create a new Builder.
     *
     * @param Model $model
     * @param mixed $items
     */
    public function __construct(Model $model, $items = [])
    {
        parent::__construct($items);
        $this->model = $model;
    }

    /**
     * @param callable|null $callback
     * @param null $default
     * @return Model|null
     */
    public function first(callable $callback = null, $default = null): ?Model
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

        $self = $this;
        $models = new Collection();
        $this->each(function ($item) use ($self, $models) {
            $models->push($self->convertToModel($item));
        });
        return $models;
    }

    /**
     * @param array $data
     * @return Model
     */
    protected function convertToModel(array $data): Model
    {
        return $this->model->newInstance()->setOriginals($data)->setAttributes($data);
    }
}
