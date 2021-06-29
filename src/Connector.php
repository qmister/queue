<?php

namespace tp5er;


use Exception;

/**
 * Class Connector
 * @package tp5er
 */
abstract class Connector
{


    /**
     * @var
     */
    public $queueName;

    /**
     * @param mixed $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * @param $message
     * @return mixed
     */
    abstract public function push($message);

    /**
     * @return mixed
     */
    abstract public function run();


    /**
     * @return string
     */
    public function buildMessageId()
    {
        return uniqid('', true);
    }

    /**
     * @param MessageEvent $event
     * @return mixed
     */
    public function runMessage(MessageEvent $event)
    {
        $job = $event->decryptMessage();
        if ($job instanceof JobInterface) {
            return call_user_func([$job, 'execute'], $this->queueName);
        }
        return true;
    }


}