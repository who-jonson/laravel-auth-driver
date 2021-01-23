<?php


namespace WhoJonson\LaravelAuth\Contracts;

use Illuminate\Support\Collection;
use WhoJonson\LaravelAuth\Abstracts\Model as AuthModel;
use Illuminate\Contracts\Auth\Authenticatable;
use WhoJonson\LaravelAuth\Exceptions\LaravelAuthException;

interface Model
{
    /**
     * @return AuthModel[]|Authenticatable[]|Collection|null
     */
    public static function all();

    /**
     * @param string|int $id
     * @return AuthModel|Authenticatable|null
     */
    public static function find($id);

    /**
     * @param string|int $id
     * @return AuthModel|Authenticatable|null
     *
     * @throws LaravelAuthException
     */
    public static function findOrFail($id);

    /**
     * @param array $data
     *
     * @return AuthModel|Authenticatable
     *
     * @throws LaravelAuthException
     */
    public static function create(array $data);

    /**
     * @param string|int $id
     * @param array $data
     *
     * @return AuthModel|Authenticatable|bool|null
     *
     * @throws LaravelAuthException
     */
    public static function update($id, array $data);

    /**
     * @param $id
     * @return bool
     */
    public static function destroy($id);

    /**
     * @return bool
     *
     * @throws LaravelAuthException
     */
    public function save();

    /**
     * @return bool
     */
    public function delete();

    /**
     * @return AuthModel|Authenticatable
     */
    public function refresh();
}
