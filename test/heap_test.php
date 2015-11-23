<?php

$loader = new \Phalcon\Loader();
$loader->registerNamespaces(['ReactQue'=>dirname(__DIR__).'/react_phalcon'])->register();

new ReactQue\React();

$h = new ReactQue\TaskHeap();
$h2 = clone $h;
$a = [];

foreach ($h2 as $v) {
    $a[]=$v->toArray();
}
print_r($a);
// $tasks = ReactQue\TimerModel::getTasks(1);

// foreach ($tasks->items as $v) {
//     // print_r($v->toArray());
//     $h->insert($v);
// }
// echo $h->count().PHP_EOL;

// print_r($h->next());

// foreach ($h as $v) {
    
//     print_r($v->toArray());
//     echo PHP_EOL;
// }

echo $h->count().PHP_EOL;
