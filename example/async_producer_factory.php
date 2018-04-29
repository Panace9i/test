<?php
require_once __DIR__ . '/config.php';

use Panace9i\Queue\RabbitMQ\Producer\Factory;

$entity = Factory::getInstance(Factory::ADAPTER_ASYNC);
$result = $entity->execute('request-' . date('H:i:s'), 'rmq_queue_async_test');

print_r($result);




