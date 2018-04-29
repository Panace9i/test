<?php
require_once __DIR__ . '/config.php';

use Panace9i\Queue\RabbitMQ\Producer\Adapter\Async AS Producer;

$entity = new Producer();
$result = $entity->execute('request-' . date('H:i:s'), 'rmq_queue_async_test');

print_r($result);




