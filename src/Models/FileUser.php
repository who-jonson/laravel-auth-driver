<?php


namespace WhoJonson\LaravelAuth\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable as AuthorizableTrait;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use WhoJonson\LaravelAuth\Traits\Authenticatable as AuthenticatableTrait;
use WhoJonson\LaravelAuth\Models\Model;

class FileUser extends Model implements
    Authenticatable,
    AuthorizableContract
{
    use AuthenticatableTrait, AuthorizableTrait;

    /**
     * File path where data are being stored
     *
     * @var string
     */
    protected $fileName = 'file_user';
}
