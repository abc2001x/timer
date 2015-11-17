<?php
namespace ReactQue;

class Ctrl extends BaseCtrl{

    public function hello(){
        $loop = $this->loop;

        $di = self::getDI();
        $timer = $di->getTimer();
        $content ='<ul>';
        $que = $timer->getQue();
        
        foreach ($que as $item) {
            // echo $item->exec_time;
            $content .= '<li>'.'id:'.$item->id.' '.$item->class .':'.$item->method.' 执行时间:'.date('Y-m-d H:i:s',$item->exec_time).'</li>';
                   
        }
        $html = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    %s
                </body>
                </html>';
        $content.='</ul>';
        $content = sprintf($html,$content);
        return $content;
    }

    public function not_found(){
        return 'not found!';   
    }

    public function add_timer(){
        $data = $this->getData();
        if (!$data) {
            return 'fail';
        }

        $data = json_decode($data);
       
        $class = $data->class;
        $method = $data->method;
        $params = $data->params;
        $exec_time = $data->exec_time;
        $now = time();
        $loop = $this->loop;
        
        // print_r(Timer::$tasks);
        $di = self::getDI();
        $timer = $di->getTimer();

        $task = $timer->addTask($class,$method,$exec_time,$params);
        
        if (!$task) {
            return 'fail';
        }
        // $futureTime = $exec_time-$now;

        // $executor = Timer::getExecutor();
        
        // if (condition) {
        //     # code...
        // }
        // // echo $futureTime.PHP_EOL;
        // $future = $loop->addTimer($futureTime,function(){
        //     $params = json_decode($task->params);
        //     $className = $task->class;
        //     $methodName = $task->method;

        //     if (method_exists($className,$methodName)) {
        //         \Library\Utils::call_func($className,$methodName,$params);
        //         $task->delete();
        //         // \ReactQue\Timer::cleanTask();
        //     }
        //     // $method->invokeArgs
        //     // $o = new $className();
        //     // $o->{$methodName}();
        //     // call_user_func_array([$className,$methodName], $params);
        //     echo "timer called".PHP_EOL;
        // });

        return 'ssss';
    }
}