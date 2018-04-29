<?php

namespace Panace9i\Queue\RabbitMQ\Consumer;

use Panace9i\Queue\RabbitMQ\Config\Config;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class ConsumerAbstract
 * @package Panace9i\Queue\RabbitMQ\Consumer
 */
abstract class ConsumerAbstract
{
    /** @var AMQPStreamConnection */
    protected $connection;
    /** @var \PhpAmqpLib\Channel\AMQPChannel */
    protected $channel;

    /**
     * ConsumerAbstract constructor.
     */
    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(Config::getHost(), Config::getPort(), Config::getUser(), Config::getPassword());
    }

    /**
     * @param string $queue      #имя очереди, такое же, как и у отправителя
     * @param bool   $passive    #пассивный
     * @param bool   $durable    #надёжный (при отключении сервера очередь будет сохранена)
     * @param bool   $exclusive  #эксклюзивный
     * @param bool   $autodelete #автоудаление
     *
     * @return ConsumerAbstract
     */
    public function addChanel($queue, $passive = false, $durable = false, $exclusive = false, $autodelete = false)
    {
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $autodelete);

        return $this;
    }

    /**
     * @param string $queue             #очередь
     * @param        $callbackFunction  #функция обратного вызова - метод, который будет принимать сообщение
     * @param string $consumerTag       #тег получателя - Идентификатор получателя, валидный в пределах текущего
     *                                  канала. Просто строка
     * @param bool   $noLocal           #не локальный - TRUE: сервер не будет отправлять сообщения соединениям, которые
     *                                  сам опубликовал
     * @param bool   $noAcknowledgment  #без подтверждения - отправлять соответствующее подтверждение обработчику, как
     *                                  только задача будет выполнена. Если TRUE то в случае падения consimer'a задача
     *                                  не будет выполнена но будет удалена из очереди. Если FALSE - то в случае
     *                                  падения consumer'а задача будет передана другому воркеру
     * @param bool   $exclusive         #эксклюзивная - к очереди можно получить доступ только в рамках текущего
     *                                  соединения
     * @param bool   $noWait            #не ждать - TRUE: сервер не будет отвечать методу. Клиент не должен ждать
     *                                  ответа
     *
     * @return ConsumerAbstract
     */
    public function addConsume($queue, $callbackFunction, $consumerTag = '', $noLocal = false, $noAcknowledgment = false, $exclusive = false, $noWait = false)
    {
        $this->channel->basic_consume($queue, $consumerTag, $noLocal, $noAcknowledgment, $exclusive, $noWait, $callbackFunction);

        return $this;
    }

    /**
     * @param null $prefetchSize
     * @param int  $prefetchCount # не отдавать подписчику единовременно более <n> сообщениий.
     * @param null $aGlobal
     *
     * @return ConsumerAbstract
     */
    public function addQos($prefetchSize = null, $prefetchCount = 1, $aGlobal = null)
    {
        $this->channel->basic_qos($prefetchSize, $prefetchCount, $aGlobal);

        return $this;
    }

    /**
     * @return ConsumerAbstract
     */
    public function wait()
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        return $this;
    }

    /**
     * @return ConsumerAbstract
     */
    public function channelClose()
    {
        $this->channel->close();

        return $this;
    }

    /**
     * @return ConsumerAbstract
     */
    public function connectionClose()
    {
        $this->connection->close();

        return $this;
    }
}