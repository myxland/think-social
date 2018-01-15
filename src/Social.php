<?php
namespace myxland\social;

use InvalidArgumentException;
use myxland\social\library\Channel;

class Social
{
    /** @var Channel[] */
    protected static $channels = [];

    /**
     * 获取一个社会化渠道
     * @param string $name
     * @return Channel
     */
    public static function channel($name)
    {
        $name = strtolower($name);
        if (!isset(self::$channels[$name])) {
            self::$channels[$name] = self::buildChannel($name);
        }

        return self::$channels[$name];
    }

    /**
     * 创建渠道
     * @param string $name
     * @return Channel
     */
    protected static function buildChannel($name)
    {
        $className = "\\myxland\\social\\library\\channel\\" . ucfirst($name);

        $channels = config('social.channels');
        if (class_exists($className) && isset($channels[$name])) {
            return new $className($channels[$name]);
        }
        throw new InvalidArgumentException("Channel [{$name}] not supported.");
    }

}