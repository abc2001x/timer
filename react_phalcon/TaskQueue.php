<?php
namespace ReactQue;
/**
 * 链表,每次取时间最小的任务用定时器执行,
 */
class TaskQueue extends \stdClass {

    private $config=['max_length'=>10];

    public $q;
    public $log;
    public function __construct($params=null){
        $log = new \Phalcon\Logger\Adapter\File(__DIR__.'/'.date('Y-m-d').".log");
        $this->log = $log;
        if ($params) {
            $this->config = array_merge($this->config,$params);
        }
        $this->q = array();
        $this->initQueue();        

    }

    public function getQue(){
        return $this->q;
    }

    private function initQueue(){
        echo "init queue line:".__LINE__.PHP_EOL;
        $this->pushByDb();
    }

    public function pushByDb(){
        $perpage = $this->config['max_length']-count($this->q);
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
            $this->q[]=$v;
        }
        $this->log->info("数据库加载完毕 this-q:".json_encode($this->q));
    }

    public function getTask(){
        if (empty($this->q)) {
            $this->pushByDb();
        }
        $length = count($this->q);
        if (0==$length) {
            return false;
        }

        return $this->q[0];
    }

    public function popTask(){
        if (empty($this->q)) {
            $this->pushByDb();
        }
        $length = count($this->q);
        if (0==$length) {
            return false;
        }
        $task = array_shift($this->q);

        if (!$task->id) {
            
            echo "弹出失败".__LINE__.json_encode($this->q).PHP_EOL;
            return false;
        }
        // echo "task id:$task->id 弹出".__LINE__.PHP_EOL;
        // print_r($this->q);
        return $task;
    }

    private function insertAt($index, $value) {
        // echo "在 $index 位 插入 1 个任务".json_encode($value).PHP_EOL;
        $this->log->info("在 $index 位 插入 1 个任务");
        //中间截取,再重新组合
        array_splice($this->q, $index, 0, [$value]);
        // $this->log->info('insert after'.json_encode($this->q));
        if (count($this->q>$this->config['max_length'])) {
            $this->q = array_slice($this->q,0,$this->config['max_length']);   
        }
        
        
        return $this->q;
    }
    
    public function insert($task){
        $que = $this->q;
        $length = count($this->q);
        if ($length==0 &&empty($task)&&!$task) {
            $this->log->info("空任务 ");
            //空队列插入
            // echo "在 0位 插入 1 个任务".__LINE__.PHP_EOL;
            $this->q[]=$task;
            return;
        }
        $pos=null;

        for ($i=0; $i < $length; $i++) { 
            if ($task->exec_time < $que[$i]->exec_time) {
                //出现更小的元素,新元素占用此位置(前插),并且停止循环
                $pos=$i;
                // die($pos);
                break;
            }
            elseif ($task->exec_time == $que[$i]->exec_time) {
                //出现相同元素,新元素后置一个位置(后插)
               $pos=!$pos ? $i+1 : $pos; 
               continue;
            }
            else
            {
                if (!!$pos) {
                    break;
                }
            }
        }
        
        if ($pos!==null) {
            // $this->log->info("插入时找到位置$pos ".json_encode($task));
            $this->insertAt($pos,$task);
            return ; 
        }
        //遍历所有元素后,没找出位置时 //只有当前que中长度未超长时添加
        if (!$pos && $length < $this->config['max_length']) {
            $this->insertAt($length,$task);
        }
        
        return;

    }
}