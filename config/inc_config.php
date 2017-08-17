<?php

$GLOBALS['config']['db'] = array(
    'host'  => '192.168.31.249',
    'port'  => 3306,
    'user'  => 'root',
    'pass'  => 'chukui',
    'name'  => 'robot',
);

$GLOBALS['config']['redis'] = array(
    'host'      => '192.168.31.249',
    'port'      => 6379,
    'pass'      => '',
    'prefix'    => 'phpspider',
    'timeout'   => 30,
);

include "inc_mimetype.php";
