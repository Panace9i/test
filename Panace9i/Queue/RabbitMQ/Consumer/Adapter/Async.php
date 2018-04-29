<?php

namespace Panace9i\Queue\RabbitMQ\Consumer\Adapter;

use Panace9i\Queue\RabbitMQ\Consumer\ConsumerInterface;
use Panace9i\Queue\RabbitMQ\Consumer\ConsumerAbstract;

/**
 * Class Async
 * @package Panace9i\Queue\RabbitMQ\Consumer\Adapter\
 */
class Async extends ConsumerAbstract implements ConsumerInterface
{
    /**
     * @param string $queue
     * @param        $callbackFunction
     */
    public function listen($queue, $callbackFunction)
    {
        $this
          ->addChanel($queue, false, true)
          ->addQos()
          ->addConsume($queue, $callbackFunction)
          ->wait();

        $this->channelClose();
        $this->connectionClose();
    }
}
