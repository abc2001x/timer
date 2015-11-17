<?php
namespace ReactQue;

class BaseCtrl{
    private $request ;
    private $response ;
    public $loop;
    private $data;

    private static $di;
    public static function setDI($di){
        self::$di=$di;
    }
    public static function getDI(){
        return self::$di;   
    }

    public function __construct($request,$response){

        $this->request=$request;
        $this->response = $response;
        $this->loop = React::getLoop();

        $response->writeHead(200,['Content-Type' => 'text/html;']);
    }

    public function getData(){
        return $this->data;
    }

    public function setData($data){
        $this->data = $data;
    }

}