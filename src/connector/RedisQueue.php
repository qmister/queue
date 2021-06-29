<?php

namespace tp5er\connector;

use tp5er\Connector;
use tp5er\MessageEvent;

/**
 * Class RedisQueue
 * @package tp5er\connector
 */
class RedisQueue extends Connector
{


    /**
     * @var \Redis|null
     */
    protected static $redis = null;

    /**
     * @var array
     */
    protected $config = [
        'persistent' => 'connect',
        'host' => '127.0.0.1',
        'port' => '6379',
        'timeout' => 60,
        'password' => '',
        'select' => '',
    ];

    /**
     * RedisQueue constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $queueName = isset($this->config['queueName']) ? $this->config['queueName'] : "queue";
        $this->setQueueName($queueName);
        self::$redis = $this->redisConnect($this->config);
    }

    /**
     * @param $config
     * @return \Redis
     */
    protected function redisConnect($config)
    {
        $redis = new \Redis();
        $func = $config['persistent'] ? 'pconnect' : 'connect';
        $redis->$func($config['host'], $config['port'], $config['timeout']);
        if ($config['password'] != '') {
            $redis->auth($config['password']);
        }
        if ($config['select'] != 0) {
            $redis->select($config['select']);
        }
        return $redis;
    }


    /**
     * @param $message
     * @return mixed|string
     */
    public function push($message)
    {
        $id = $this->buildMessageId();
        self::$redis->hSet("$this->queueName.message", $id, $message);
        self::$redis->lPush("$this->queueName.waiting", $id);
        return $id;
    }

    /**
     * @return mixed|void
     */
    public function run()
    {
        // TODO: Implement run() method.
        while (true) {
            $payload = $this->reserve();
            if ($payload != null) {
                list($id, $message) = $payload;
                if ($this->runMessage(new MessageEvent($message, $id))) {
                    $this->delete($id);
                }
            }
        }
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        self::$redis->hDel("$this->queueName.message", $id);
    }

    /**
     * @return array|null
     */
    public function reserve()
    {
        $id = self::$redis->rPop("$this->queueName.waiting");
        if (!$id) {
            return null;
        }
        $message = self::$redis->hGet("$this->queueName.message", $id);
        return [$id, $message];
    }
}