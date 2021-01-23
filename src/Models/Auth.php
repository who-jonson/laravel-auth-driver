<?php


namespace WhoJonson\LaravelAuth\Models;

use WhoJonson\LaravelAuth\Abstracts\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable as AuthorizableTrait;
use WhoJonson\LaravelAuth\Traits\Authenticatable as AuthenticatableTrait;

class Auth extends Model implements AuthenticatableContract, AuthorizableContract
{
    use AuthenticatableTrait, AuthorizableTrait;
}
