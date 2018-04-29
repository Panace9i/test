<?php

namespace Panace9i\Queue\RabbitMQ\Consumer;

class Factory
{
    const ADAPTER_SYNC  = 'Sync';
    const ADAPTER_ASYNC = 'Async';

    /**
     * @param $adapterName
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getInstance($adapterName)
    {
        $className = 'Panace9i\Queue\RabbitMQ\Consumer\Adapter\\' . $adapterName;
        if (!class_exists($className)) {
            throw new \Exception("Adapter not found", 1);
        }

        $class = new $className();
        if (!($class instanceof ConsumerInterface)) {
            throw new \Exception("{$className} not instance of ConsumerInterface", 1);
        }

        return $class;
    }
}