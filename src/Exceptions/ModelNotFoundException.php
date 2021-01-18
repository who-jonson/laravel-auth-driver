<?php


namespace WhoJonson\LaravelAuth\Exceptions;


class ModelNotFoundException extends LaravelAuthException
{
    /**
     * ModelNotFoundException constructor.
     * @param string $model
     */
    public function __construct(string $model)
    {
        parent::__construct('"' . $model . '" not found with given query');
    }
}
