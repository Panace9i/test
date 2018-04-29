<?php
require_once __DIR__ . '/config.php';

use Panace9i\Queue\RabbitMQ\Consumer\Adapter\Async AS Consumer;
use Panace9i\Queue\RabbitMQ\Handler\HandlerAbstract;

class AsyncTest extends HandlerAbstract
{
    public function listen($request)
    {
        print_r($request->body);
        echo PHP_EOL;

        return $this->reply($request->body, $request);
    }
}

$entity = new Consumer();
$entity->listen('rmq_queue_async_test', [new AsyncTest, 'listen']);