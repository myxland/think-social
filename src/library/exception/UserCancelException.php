<?php

namespace myxland\social\library\exception;

class UserCancelException extends Exception
{
    public function __construct()
    {
        parent::__construct("用户取消");
    }
}