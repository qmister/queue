<?php

namespace tp5er;


/**
 * Class MessageEvent
 */
class MessageEvent
{
    /**
     * @var
     */
    public $id;
    /**
     * @var
     */
    public $job;


    /**
     * MessageEvent constructor.
     * @param $job
     * @param int $id
     */
    public function __construct($job, $id = 0)
    {
        $this->job = $job;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function encryptMessage()
    {
        return serialize($this->job);
    }

    public function decryptMessage()
    {
        return unserialize($this->job);
    }
}