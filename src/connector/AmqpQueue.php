<?php


namespace tp5er\connector;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use tp5er\Connector;
use tp5er\MessageEvent;

/**
 * Class AmqpQueue
 * @package tp5er\connector
 */
class AmqpQueue extends Connector
{

    /**
     * @var
     */
    protected $connection;
    /**
     * @var
     */
    protected $channel;
    /**
     * @var array
     */
    protected $config = [
        'host' => 'localhost',
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest',
        'exchangeName' => 'exchange',
        'queueName' => 'queue',
        'vhost' => '/'
    ];

    /**
     * AmqpQueue constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $queueName = isset($this->config['queueName']) ? $this->config['queueName'] : "queue";
        $this->setQueueName($queueName);

        // 关闭链接
        register_shutdown_function(function () {
            $this->close();
        });
    }

    /**
     *
     */
    public function open()
    {
        if ($this->channel) {
            return;
        }
        $conf = $this->config;
        $this->connection = new AMQPStreamConnection(
            $conf['host'],
            $conf['port'],
            $conf['user'],
            $conf['password'],
            $conf['vhost']
        );
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare(
            $conf['queueName'],
            false,
            true,
            false,
            false
        );
        $this->channel->exchange_declare(
            $conf['exchangeName'],
            'direct',
            false,
            true,
            false
        );
        $this->channel->queue_bind($conf['queueName'], $conf['exchangeName']);
    }


    /**
     * @param $message
     * @return string
     */
    public function push($message)
    {
        // TODO: Implement push() method.
        $conf = $this->config;
        $this->open();
        $id = $this->buildMessageId();
        $this->channel->basic_publish(
            new AMQPMessage("$message", [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
                'message_id' => $id,
            ]),
            $conf['exchangeName']
        );
        return $id;
    }

    /**
     *
     */
    public function run()
    {
        $conf = $this->config;
        $this->open();
        $callback = function (AMQPMessage $payload) use (&$conf) {
            $id = $payload->get('message_id');
            $message = $payload->body;
            if ($this->runMessage(new MessageEvent($message, $id))) {
                $payload->delivery_info['channel']->basic_ack($payload->delivery_info['delivery_tag']);
            }
        };
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($conf['queueName'], '', false, false, false, false, $callback);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     *
     */
    public function close()
    {
        if (!$this->channel) {
            return;
        }
        $this->channel->close();
        $this->connection->close();
    }
}