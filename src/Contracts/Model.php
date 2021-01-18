<?php


namespace WhoJonson\LaravelAuth\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;
use WhoJonson\LaravelAuth\Exceptions\LaravelAuthException;
use WhoJonson\LaravelAuth\Exceptions\ModelNotFoundException;

interface Model
{
    /**
     * @return \WhoJonson\LaravelAuth\Models\Model[]|Authenticatable[]|Collection|null
     */
    public static function all();

    /**
     * @param string|int $id
     * @return \WhoJonson\LaravelAuth\Models\Model|Authenticatable|null
     */
    public static function find($id);

    /**
     * @param string|int $id
     * @return \WhoJonson\LaravelAuth\Models\Model|Authenticatable|null
     *
     * @throws ModelNotFoundException
     */
    public static function findOrFail($id);

    /**
     * @param array $data
     *
     * @return \WhoJonson\LaravelAuth\Models\Model|Authenticatable
     *
     * @throws LaravelAuthException
     */
    public static function create(array $data);

    /**
     * @param string|int $id
     * @param array $data
     *
     * @return \WhoJonson\LaravelAuth\Models\Model|Authenticatable|bool|mixed
     *
     * @throws LaravelAuthException
     */
    public static function update($id, array $data);

    /**
     * @param $id
     * @return bool
     */
    public static function delete($id);

    /**
     * @return bool
     *
     * @throws LaravelAuthException
     */
    public function save();

    /**
     * @return \WhoJonson\LaravelAuth\Models\Model|Authenticatable
     */
    public function refresh();
}
