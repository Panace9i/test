<?php

namespace Panace9i\Queue\RabbitMQ\Producer;

/**
 * Interface ProducerInterface
 * @package Panace9i\Queue\RabbitMQ\Producer
 */
interface ProducerInterface
{
    /**
     * @param string $message
     * @param string $queue
     *
     * @return mixed
     */
    public function execute($message, $queue);
}