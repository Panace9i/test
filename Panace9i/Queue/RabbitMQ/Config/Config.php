<?php

namespace Panace9i\Queue\RabbitMQ\Config;

class Config
{
    private static $host     = 'localhost';
    private static $port     = 5672;
    private static $user     = 'guest';
    private static $password = 'guest';

    /**
     * @return Config
     */
    public static function init()
    {
        return new self();
    }

    /**
     * @return string
     */
    public static function getHost()
    {
        return self::$host;
    }

    /**
     * @param string $host
     *
     * @return Config
     */
    public function setHost($host)
    {
        self::$host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public static function getPort()
    {
        return self::$port;
    }

    /**
     * @param int $port
     *
     * @return Config
     */
    public function setPort($port)
    {
        self::$port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public static function getUser()
    {
        return self::$user;
    }

    /**
     * @param string $user
     *
     * @return Config
     */
    public function setUser($user)
    {
        self::$user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public static function getPassword()
    {
        return self::$password;
    }

    /**
     * @param string $password
     *
     * @return Config
     */
    public function setPassword($password)
    {
        self::$password = $password;

        return $this;
    }
}