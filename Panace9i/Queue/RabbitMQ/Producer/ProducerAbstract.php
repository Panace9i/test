<?php
namespace Panace9i\Queue\RabbitMQ\Producer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Panace9i\Queue\RabbitMQ\Config\Config;

/**
 * Class ProducerAbstract
 * @package Panace9i\Queue\RabbitMQ\Producer
 */
abstract class ProducerAbstract
{
    /** @var AMQPStreamConnection */
    protected $connection;
    /** @var \PhpAmqpLib\Channel\AMQPChannel */
    protected $channel;
    /** @var string */
    protected $callback_queue;
    protected $response;

    /** ID транзакции */
    protected $correlation_id = null;

    /** Режим устойчивости. При падение сервера RMQ - очереди и сообщения сохраняться */
    private $durable_mode = false;

    /**
     * ProducerAbstract constructor.
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
     * @return ProducerAbstract
     */
    final public function addChanel($queue, $passive = false, $durable = false, $exclusive = false, $autodelete = false)
    {
        $this->channel      = $this->connection->channel();
        $this->durable_mode = $durable;
        $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $autodelete);

        return $this;
    }

    /**
     * Формирует канал для ответного сообщения
     *
     * @return ProducerAbstract
     */
    public function addCallbackChanel()
    {
        list($this->callback_queue, ,) = $this->channel->queue_declare("");

        return $this;
    }

    /**
     * Создаёт Потребитель
     *
     * @param string   $queue             #очередь
     * @param Callable $callbackFunction  #функция обратного вызова - метод, который будет принимать сообщение
     * @param string   $consumerTag       #тег получателя - Идентификатор получателя, валидный в пределах текущего
     *                                    канала. Просто строка
     * @param bool     $noLocal           #не локальный - TRUE: сервер не будет отправлять сообщения соединениям, которые
     *                                    сам опубликовал
     * @param bool     $noAcknowledgment  #без подтверждения - отправлять соответствующее подтверждение обработчику, как
     *                                    только задача будет выполнена. Если TRUE то в случае падения consimer'a задача
     *                                    не будет выполнена но будет удалена из очереди. Если FALSE - то в случае
     *                                    падения consumer'а задача будет передана другому воркеру
     * @param bool     $exclusive         #эксклюзивная - к очереди можно получить доступ только в рамках текущего
     *                                    соединения
     * @param bool     $noWait            #не ждать - TRUE: сервер не будет отвечать методу. Клиент не должен ждать
     *                                    ответа
     *
     * @return ProducerAbstract
     */
    public function addConsume($queue, Callable $callbackFunction, $consumerTag = '', $noLocal = false, $noAcknowledgment = false, $exclusive = false, $noWait = false)
    {
        $this->channel->basic_consume($queue, $consumerTag, $noLocal, $noAcknowledgment, $exclusive, $noWait, $callbackFunction);

        return $this;
    }

    /**
     * Отправляет сообщение
     *
     * @param string $message
     * @param string $routingKey
     * @param string $exchange
     * @param array  $messageParameters
     *
     * @return ProducerAbstract
     */
    public function publish($message, $routingKey, $exchange = '', array $messageParameters = [])
    {
        $this->response       = null;
        $this->correlation_id = uniqid();

        if ($this->durable_mode && !isset($messageParameters['delivery_mode'])) {
            $messageParameters['delivery_mode'] = 2; # make message persistent
        }

        $msg = new AMQPMessage($message, array_merge($messageParameters, [
          'correlation_id' => $this->correlation_id,
        ]));

        $this->channel->basic_publish($msg,
          $exchange,
          $routingKey
        );

        return $this;
    }

    /**
     * @param null $prefetchSize
     * @param int  $prefetchCount # не отдавать подписчику единовременно более <n> сообщениий.
     * @param null $aGlobal
     *
     * @return ProducerAbstract
     */
    public function addQos($prefetchSize = null, $prefetchCount = 1, $aGlobal = false)
    {
        $this->channel->basic_qos($prefetchSize, $prefetchCount, $aGlobal);

        return $this;
    }

    /**
     * Ожидает получение ответа от запроса (синхронный режим)
     *
     * @return ProducerAbstract
     */
    public function wait()
    {
        while (!$this->response) {
            $this->channel->wait();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function channelClose()
    {
        $this->channel->close();

        return $this;
    }

    /**
     * @return ProducerAbstract
     */
    public function connectionClose()
    {
        $this->connection->close();

        return $this;
    }

    /**
     * Функция обработки ответа от callback-канала
     *
     * @param $rep
     */
    public function on_response($rep)
    {
        if ($rep->get('correlation_id') == $this->correlation_id) {
            $this->response = $rep->body;
        }
    }
}