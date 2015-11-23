<?php
namespace ReactQue;
class Timer
{
    public $executor = null;//定时器
    public $tasks=null;//TaskQueue

    const TIMER_API = 'http://localhost:1337/add_timer';

    // public function __construct(){

    // }
    
    public function __construct(){
        $log = new \Phalcon\Logger\Adapter\File(__DIR__.'/'.date('Y-m-d').".log");
        $this->log = $log;

        // $this->tasks = new TaskQueue();
        $this->tasks = new TaskHeap();
        $this->setExecutor();

    }

    public function getQue(){
        return $this->tasks->getQue();
    }

    //新增加的任务,是否设置为新的timer执行
    private function setExecutor(){
        $loop = React::getLoop();
        // $que = self::$tasks;
        $task = $this->tasks->getTask();//获取最小执行时间的任务
        // $this->log->info("即将执行的任务是:".json_encode($this->tasks->getQue()).'中的'.json_encode($task));
        $now = time();

        if (!$task) {
            echo '没有任务可以执行..'.PHP_EOL;
            return;
        }
        $futureTime = $task->exec_time-$now;

        if (!$this->executor || $futureTime < $this->executor->exec_time) {
            //未设置定时器时,直接设置定时器 //或新任务的执行时间更小,更新executor
            // $task = self::getTask();
            if ($this->executor&&$this->executor->isActive()) {
                //取消旧任务
                echo '取消旧任务..'.PHP_EOL;
                $this->executor->cancel();
            }
            $future = $loop->addTimer($futureTime,function(){
                        $task = $this->tasks->popTask();
                        if ($task) {
                            $this->execTask($task);
                        }else{
                            echo '没有任务弹出执行..'.PHP_EOL;
                        }
                        
                    });

            $future->exec_time =$futureTime+time();//增加执行时间字段,用于比较执行时间大小
            $this->executor=$future;
            return;
        }
        
    }

    // private static function getExecutor(){
    //     return self::$executor;
    // }

    // private static function getTasksQue(){
    //     if (!self::$tasks) {
    //         self::$tasks = new TaskQueue();
    //     }
    //     // print_r(self::$tasks);
    //     return self::$tasks;
    // }

    // private static function popTask(){
    //     if (!self::$tasks) {
    //         self::$tasks = new TaskQueue();
    //     }

    //     return self::$tasks->popTask();
    // }

    // private static function getTask(){
    //     if (!self::$tasks) {
    //         self::$tasks = new TaskQueue();
    //     }

    //     return self::$tasks->getTask();

    // }

    /**
     * 客户端定时入口
     * @author wugx
     * @version     1.0
     * @date        2015-11-17
     * @anotherdate 2015-11-17T17:07:17+0800
     * @param       [type]                   $fullClassName [description]
     * @param       [type]                   $methodName    [description]
     * @param       [type]                   $second        [description]
     * @param       array                    $params        [description]
     * @return      [type]                                  [description]
     */
    public static function requestTimer($fullClassName,$methodName,$second,$params=[]){
        $data = ['class'=>$fullClassName,'method'=>$methodName,'exec_time'=>time()+$second,'params'=>$params];
        $return = self::sendDataByCurl(self::TIMER_API,$data);
        if ('success'==$return) {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 添加定时任务,数据库中,队列中
     * @author wugx
     * @version     1.0
     * @date        2015-11-15
     * @anotherdate 2015-11-15T03:51:58+0800
     * @param       [type]                $fullClassNameAndMethod \namespace\classname:method
     * @param       int                   $second                 执行时间戳
     * @param       array                 $params                 参数
     */
    public function addTask($fullClassName,$methodName,$exec_time,$params=[]){
        if (!method_exists($fullClassName,$methodName)) {
            return false;
        }
        // echo '添加任务:'.self::$min_exec_time.PHP_EOL;
        $model = new TimerModel();
        $model->class=$fullClassName;
        $model->method=$methodName;
        $model->params=json_encode($params);
        $model->exec_time = $exec_time;

        
        if ($model->save()) {
            // $this->tasks->test($model);
            // self::setMinExecTime($model->exec_time);
            // $que = self::$tasks;
            // echo $model->id.'队列:'.json_encode($model).PHP_EOL;
            // $task = json_decode(json_encode($model->toArray()));
            $this->tasks->insert($model);//添加新任务到队列
            $this->setExecutor();//设置定时器

            return $model;
        }
        else
        {
            echo '保存失败..'.PHP_EOL;
            return false;
        }
    }

    private function execTask(TimerModel $task){
        $params = json_decode($task->params);
        $className = $task->class;
        $methodName = $task->method;

        if (method_exists($className,$methodName)) {
            Utils::call_func($className,$methodName,$params);
            // TimerModel::deleteTask($task->id);
            $task->delete();
         
        }
        echo "timer called ".$methodName.'计划时间:'.date('Y-m-d H:i:s',$task->exec_time).' 执行时间:'.date('Y-m-d H:i:s').PHP_EOL;
        $this->executor=null;
        $this->setExecutor();

    }


    // public static function cleanTask(){
    //     $tasks = TimerModel::find(['exec_time <= :time:','bind'=>['time'=>time()]]);
    //     foreach ($tasks as $v) {
    //         $className = $v->class;
    //         $methodName = $v->method;
    //         $params = json_decode($v->params,1);
    //         if (method_exists($className, $methodName)) {
    //             \Library\Utils::call_func($className, $methodName,$params);
    //             // $r = call_user_func_array([$className,$methodName], $params);
    //             echo $v->id.' 执行'.$className.'->'.$methodName.'('.$v->params.') 成功'.PHP_EOL;
    //             $v->delete();
    //         }
    //     }
    // }

    private static function sendDataByCurl($url,$data=array()){
        //对空格进行转义
        $url = str_replace(' ','+',$url);
        $ch = curl_init();
        $data_string=json_encode($data);
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_TIMEOUT,3);  //定义超时3秒钟 
         // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);    //所需传的数组用http_bulid_query()函数处理一下，就ok了
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        //执行并获取url地址的内容
        $output = curl_exec($ch);
        $errorCode = curl_errno($ch);
        //释放curl句柄
        curl_close($ch);
        if(0 !== $errorCode) {
            return false;
        }
        return $output;
    }

}