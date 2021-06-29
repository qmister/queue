<?php


class testJob implements \tp5er\JobInterface
{

    /**
     * @param $queue
     * @return mixed
     */
    public function execute($queue)
    {
        // TODO: Implement execute() method.
        echo 'testJob runing ' . time() . " queue:" . $queue . PHP_EOL;
        return true;
    }
}