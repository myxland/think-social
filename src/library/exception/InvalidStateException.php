<?php

namespace myxland\social\library\exception;

class InvalidStateException extends Exception
{
    public function __construct()
    {
        parent::__construct("未授权访问");
    }
}