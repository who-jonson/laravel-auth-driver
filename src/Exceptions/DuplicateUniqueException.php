<?php


namespace WhoJonson\LaravelAuth\Exceptions;


class DuplicateUniqueException extends LaravelAuthException
{
    /**
     * DuplicateUniqueException constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        parent::__construct('Duplicate value for the key "' . $key . '"');
    }
}
