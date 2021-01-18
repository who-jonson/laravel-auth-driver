<?php


namespace WhoJonson\LaravelAuth\Exceptions;


class UndefinedFileNameException extends LaravelAuthException
{

    /**
     * FilePathNotDefined constructor.
     */
    public function __construct()
    {
        parent::__construct('File Name not defined for User\'s File Model!');
    }
}
