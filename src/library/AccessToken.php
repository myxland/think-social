<?php

namespace myxland\social\library;

class AccessToken
{
    protected $raw;

    protected $token;

    protected function __construct($raw, $token)
    {
        $this->raw   = $raw;
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getRaw($name = null, $default = null)
    {
        if (is_null($name)) {
            return $this->raw;
        } else {
            return isset($this->raw[$name]) ? $this->raw[$name] : $default;
        }
    }

    public static function make($raw, $tokenName = 'access_token')
    {
        return new self($raw, $raw[$tokenName]);
    }
}