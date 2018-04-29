<?php

namespace Panace9i\Queue\RabbitMQ\Handler;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class HandlerAbstract
 * @package Panace9i\Queue\RabbitMQ\Handler
 */
abstract class HandlerAbstract
{
    /**
     * @param string $message
     * @param object $request
     *
     * @return bool
     */
    protected function reply($message, AMQPMessage $request)
    {
        $request
          ->delivery_info['channel']
          ->basic_ack($request->delivery_info['delivery_tag']);

        if (!$request->has('correlation_id') || !$request->has('reply_to')) {
            return false;
        }

        $msg = new AMQPMessage($message, ['correlation_id' => $request->get('correlation_id')]);
        $request
          ->delivery_info['channel']
          ->basic_publish($msg, '', $request->get('reply_to'));

        return true;
    }
}