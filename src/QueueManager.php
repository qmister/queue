<?php

namespace tp5er;

use Exception;
use tp5er\connector\AmqpQueue;
use tp5er\connector\RedisQueue;

class QueueManager
{
    protected static $connector = null;

    protected $connectors = [
        'redis' => RedisQueue::class,
        'amqp' => AmqpQueue::class
    ];

//    $config = [
//        'redis'=>[
//            'persistent' => 'connect',
//            'host' => '127.0.0.1',
//            'port' => '6379',
//            'timeout' => 60,
//            'password' => '',
//            'select' => ''
//        ]
//    ];
    public function __construct($config = [])
    {

        $connectClass = $this->connectors[key($config)];
        self::$connector = new $connectClass($config);
    }

    public function push(JobInterface $job)
    {
        $event = new MessageEvent($job);
        if (!($event->job instanceof JobInterface)) {
            throw new Exception("Job must be instance of JobInterface.");
        }
        return self::$connector->push($event->encryptMessage());
    }

    public function listen()
    {
        self::$connector->run();
    }
}