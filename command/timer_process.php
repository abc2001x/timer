<?php
// gc_collect_cycles();
// gc_enable();

// echo dirname(__DIR__).'/react_phalcon/React.php';
include dirname(__DIR__).'/react_phalcon/React.php';
$r = new \ReactQue\React();
$r->run();