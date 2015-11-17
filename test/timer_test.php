<?php

$loader = new \Phalcon\Loader();
$loader->registerNamespaces(['ReactQue'=>dirname(__DIR__).'/react_phalcon'])->register();

$p=[];
for ($i=0; $i < 10; $i++) { 
    $p[] = rand(1,10)*60;
}

foreach ($p as $v) {
    $r=\ReactQue\Utils::addTimer('\\ReactQue\\Utils','test',$v,['wuguoxuan']);
}
var_dump($r);

