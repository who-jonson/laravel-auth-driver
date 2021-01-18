<?php


namespace WhoJonson\LaravelAuth\Traits;


use Illuminate\Support\Collection;
use WhoJonson\LaravelAuth\Contracts\Model;
use WhoJonson\LaravelAuth\Exceptions\ModelNotFoundException;
use WhoJonson\LaravelAuth\Models\Builder;

trait QueryBuilder
{
    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return Builder
     */
    public static function where($key, $operator = null, $value = null): Builder
    {
        return static::query()->where($key, $operator, $value);
    }

    /**
     * @return Model[]|Collection|null
     */
    public static function all()
    {
        $data = static::query();
        if(!$data || $data->count() <= 0) {
            return null;
        }

        $self = static::instance();
        $models = new Collection();
        $data->each(function ($item) use ($self, $models) {
            $models->push($self->newInstance()->setOriginals($item)->setAttributes($item));
        });
        return $models;
    }

    /**
     * @inheritDoc
     */
    public static function find($id)
    {
        return static::query()->firstWhere((new static)->primaryKey, '=', $id);
    }

    /**
     * @inheritDoc
     */
    public static function findOrFail($id)
    {
        if($model = static::find($id)) {
            return $model;
        }
        throw new ModelNotFoundException(get_class(static::instance()));
    }

    /**
     * @inheritDoc
     */
    public static function update($id, array $data)
    {
        if($model = static::find($id)) {
            foreach ($data as $key => $value) {
                // Check if primary key
                if($key == $model->primaryKey) {
                    continue;
                }
                $model->setAttribute($key, $value);
            }
            return $model->save() ? $model : false;
        }
        throw new ModelNotFoundException(static::instance());
    }

    /**
     * @inheritDoc
     */
    public function refresh() {
        return static::find($this->primaryKey);
    }
}
