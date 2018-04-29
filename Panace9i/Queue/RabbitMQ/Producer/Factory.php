<?php

namespace Panace9i\Queue\RabbitMQ\Producer;

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
        $className = 'Panace9i\Queue\RabbitMQ\Producer\Adapter\\' . $adapterName;
        if (!class_exists($className)) {
            throw new \Exception("Adapter not found", 1);
        }

        $class = new $className();
        if (!($class instanceof ProducerInterface)) {
            throw new \Exception("{$className} not instance of ProducerInterface", 1);
        }

        return $class;
    }
}