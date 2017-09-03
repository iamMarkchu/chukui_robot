<?php
$redis = new Redis;
$redis->connect('192.168.31.249');

$redis->set('name', 'chukui');

echo $redis->get('name');