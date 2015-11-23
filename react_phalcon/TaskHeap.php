<?php
namespace ReactQue;
/**
 * 链表,每次取时间最小的任务用定时器执行,
 */
class TaskHeap extends \SplMinHeap {
    private $config=['max_length'=>30];

    public $log;
    
    public function __construct($params=null){
        $log = new \Phalcon\Logger\Adapter\File(__DIR__.'/'.date('Y-m-d').".log");
        $this->log = $log;
        if ($params) {
            $this->config = array_merge($this->config,$params);
        }
        // $this->q = array();
        $this->initQueue();        
        // parent::__construct();
    }

    public function compare(TimerModel $value1,TimerModel $value2){
        return  $value2->exec_time-$value1->exec_time;
    }

    public function getQue(){
        $q=[];
        $h = clone $this;
        foreach ($h as $v) {
            $q[]=$v;
        }
        return $q;
    }


    private function initQueue(){
        // echo "init queue line:".PHP_EOL;
        $this->pushByDb();
    }

    public function pushByDb(){
        $perpage = $this->config['max_length']- $this->count();
        if (1 > $perpage) {
            return;
        }
        $taskPaginator = TimerModel::getTasks(1,$perpage,['exec_time'=>'up','id'=>'up']);
        $tasks = $taskPaginator->items;
        
        // echo "get task $taskPaginator->total_items 个".__LINE__.PHP_EOL;
        // echo " pushdb ".json_encode($tasks).__LINE__.PHP_EOL;
        if (!$tasks) {
            echo "数据库无数据".PHP_EOL;
            return;
        }

        foreach ($tasks as $v) {
            
            // echo "this->q 入队 ".__LINE__.json_encode($v).PHP_EOL;
            $this->insert($v);
        }
        $this->log->info("数据库加载完毕:".$this->count()."条");
    }

    public function getTask(){
        if ($this->isEmpty()) {
            $this->pushByDb();
        }
        $length = $this->count();
        if (0==$length) {
            return false;
        }

        return $this->top();
    }

    public function popTask(){
        if ($this->isEmpty()) {
            $this->pushByDb();
        }
        $length = $this->count();
        if (0==$length) {
            return false;
        }
        $task = $this->extract();

        if (!$task->id) {
            
            echo "弹出失败".PHP_EOL;
            return false;
        }
        // echo "task id:$task->id 弹出".__LINE__.PHP_EOL;
        // print_r($this->q);
        return $task;
    }

}