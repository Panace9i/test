<?php
require_once __DIR__ . '/../vendor/autoload.php';

Panace9i\Queue\RabbitMQ\Config\Config::init()
  ->setHost('localhost')
  ->setPort(5672)
  ->setUser('guest')
  ->setPassword('guest');
