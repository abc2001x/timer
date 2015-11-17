<?php
namespace ReactQue;

class Utils
{
    public function test($name){
        echo "hello $name";
    
    }
    
    public static function call_func($className,$methodName,$params){
        $method = new \ReflectionMethod($className,$methodName);
        $instance = new $className();
        $method->invokeArgs($instance, $params);
    }

    public static function addTimer($className,$methodName,$second,$params=[]){
        $r = \ReactQue\Timer::requestTimer($className,$methodName,$second,$params);
        return $r;
    }
}
