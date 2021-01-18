<?php


namespace WhoJonson\LaravelAuth\Exceptions;


class NotNullViolationException extends LaravelAuthException
{
    /**
     * NotNullViolationException constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        parent::__construct('"' . $key . '" field can\'t be null.');
    }
}
