<?php

require './vendor/autoload.php';

$config = [
    'redis' => [
        'persistent' => 'connect',
        'host' => '127.0.0.1',
        'port' => '6379',
        'timeout' => 60,
        'password' => '',
        'select' => ''
    ]
];

require  'Task.php';

$id = (new \tp5er\QueueManager($config))->push(new testJob());

echo $id;

