<?php


namespace WhoJonson\LaravelAuth\Exceptions;


/**
 * Class LaravelAuthException
 * @package WhoJonson\LaravelAuth\Exceptions
 */
abstract class LaravelAuthException extends \Exception
{

    /**
     * LaravelAuthException constructor.
     *
     * @param string $message [optional]
     * @param int $code [optional]
     */
    public function __construct(string $message = 'Unknown Error!', int $code = E_USER_ERROR)
    {
        parent::__construct($message, $code);
    }
}
