<?php

namespace tp5er;

/**
 * Interface JobInterface
 */
interface JobInterface
{
    /**
     * @param $queue
     * @return mixed
     */
    public function execute($queue);
}