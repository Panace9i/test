<?php

namespace Panace9i\Queue\RabbitMQ\Consumer;

interface ConsumerInterface
{
    /**
     * @param string $queue
     * @param        $callbackFunction
     *
     * @return mixed
     */
    public function listen($queue, $callbackFunction);
}