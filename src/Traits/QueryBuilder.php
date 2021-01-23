<?php


namespace WhoJonson\LaravelAuth\Traits;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use WhoJonson\LaravelAuth\Abstracts\Model;
use WhoJonson\LaravelAuth\Exceptions\LaravelAuthException;
use WhoJonson\LaravelAuth\Exceptions\ModelNotFoundException;
use WhoJonson\LaravelAuth\Support\Builder;

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
        return $data->get();
    }

    /**
     * @param string|int $id
     * @return Model|Authenticatable|null
     */
    public static function find($id)
    {
        if($data = static::findData($id)) {
            return static::instance($data);
        }
        return null;
    }

    /**
     * @param string|int $id
     * @return Model|Authenticatable|null
     *
     * @throws LaravelAuthException
     */
    public static function findOrFail($id)
    {
        if($model = static::find($id)) {
            return $model;
        }
        throw new ModelNotFoundException(static::class);
    }

    /**
     * @param string|int $id
     * @param array $data
     *
     * @return Model|Authenticatable|bool|null
     *
     * @throws LaravelAuthException
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

            return $model->save() ? $model->refresh() : false;
        }
        throw new ModelNotFoundException(static::class);
    }

    /**
     * @return Model|Authenticatable
     */
    public function refresh() {
        return static::find($this->getAttribute($this->primaryKey));
    }

    /**
     * @inheritDoc
     */
    public static function create(array $data)
    {
        $model = static::instance($data);
        $model->save();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public static function destroy($id): bool
    {
        if($model = static::find($id)) {
            return $model->delete();
        }
        return false;
    }
}
