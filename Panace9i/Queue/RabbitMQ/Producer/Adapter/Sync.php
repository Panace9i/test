<?php

namespace Panace9i\Queue\RabbitMQ\Producer\Adapter;

use Panace9i\Queue\RabbitMQ\Producer\ProducerInterface;
use Panace9i\Queue\RabbitMQ\Producer\ProducerAbstract;

/**
 * Class Sync
 * @package Panace9i\Queue\RabbitMQ\Producer\Adapter
 */
class Sync extends ProducerAbstract implements ProducerInterface
{
    /**
     * @param string $message
     * @param string $queue
     *
     * @return mixed
     */
    public function execute($message, $queue)
    {
        $result = $this
          ->addChanel($queue)
          ->addCallbackChanel()
          ->addConsume($this->callback_queue, [$this, 'on_response',])
          ->publish($message, $queue, '', ['reply_to' => $this->callback_queue,])
          ->wait()
          ->response;

        $this->channelClose();
        $this->connectionClose();

        return $result;
    }
}
