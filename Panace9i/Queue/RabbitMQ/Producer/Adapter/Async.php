<?php

namespace Panace9i\Queue\RabbitMQ\Producer\Adapter;

use Panace9i\Queue\RabbitMQ\Producer\ProducerInterface;
use Panace9i\Queue\RabbitMQ\Producer\ProducerAbstract;

/**
 * Class Async
 * @package Panace9i\Queue\RabbitMQ\Producer\Adapter
 */
class Async extends ProducerAbstract implements ProducerInterface
{
    /**
     * @param        $message
     * @param string $queue
     *
     * @return bool
     */
    public function execute($message, $queue)
    {
        $this->addChanel($queue, false, true)->addQos()->publish($message, $queue);
        $this->channelClose();
        $this->connectionClose();

        return true;
    }
}
