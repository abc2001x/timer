<?php
namespace ReactQue;
//加载公共类命名空间,加载后的用法如下:
require __DIR__.'/../vendor/autoload.php';
use Phalcon\Mvc\Router;
use Phalcon\Loader;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;
class React{
    private static $loop=null;
    const PORT = 1337;

    private $router=null;

    public static function getLoop(){
        if (!self::$loop) {
            self::$loop = \React\EventLoop\Factory::create();
        }
        return self::$loop;
    }

    public function __construct(){
        if (!self::$loop) {
            self::$loop = \React\EventLoop\Factory::create();
        }
        
        $loader = new Loader();
        $loader->registerNamespaces(['ReactQue'=>__DIR__])->register();;
        
        $this->router = new Router();
        $this->router->add(
            "/:action/:params",
            array(
                // 'namespace'  => 'Backend\Controllers',
                'controller' => 'Ctrl',
                'action'     => 1,
                'params'     => 2,
            )
        );

        $di = new \Phalcon\DI\FactoryDefault();
        $this->initPersistentDB($di);
        //定时任务
        $this->initTimer($di);
        BaseCtrl::setDI($di);
    }


    public function initTimer($di){
        $di->set('timer',function(){
            $timer = new Timer();
            return $timer;
        },true);
        $di->getTimer();
    }

    public function initPersistentDB($di){   
        // Setup the database service
        $di->set('db', function () {
            $eventsManager = new EventsManager();
            $logger = new FileLogger(__DIR__.'/'.date('Y-m-d').'.sql.log');    
            $eventsManager->attach('db', function ($event, $connection) use ($logger) {
               
                if ($event->getType() == 'beforeQuery') {

                    $logger->info($connection->getSQLStatement());
                }
            });
            $db = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                "host"     => "127.0.0.1",
                "username" => "root",
                "password" => "123456",
                "dbname"   => "rookie",
                "charset"  => "utf8",
                "persistent"=>true,
            ));
            $db->setEventsManager($eventsManager);
            return $db;
        });
    }

    private function handle($request,$response,$data=null){
        $path = $request->getPath();
        $router = $this->router;
        $router->handle($path);

        $controller = $router->getControllerName();
        $className = 'ReactQue\\'.$controller;
        $action =  $router->getActionName();
        $ctrl = null;
        if (!$controller || !$action) {
            $ctrl = new Ctrl($request,$response);
            return $ctrl->not_found();
        }

        if(class_exists($className)){
            $ctrl = new $className($request,$response);
            $params = $router->getParams();

            if (method_exists($ctrl,$action)) {
                try {
                    $ctrl->setData($data);
                    $result = call_user_func_array([$ctrl,$action], $params);    
                } catch (Exception $e) {
                    $result = 'fail';
                }
                
                return $result;

            }
            else
            {
                return $ctrl->not_found();
            }
        }

        $ctrl = new Ctrl($request,$response);
        return $ctrl->not_found();
    }

    public function run(){
        $socket = new \React\Socket\Server(self::$loop);
        $http = new \React\Http\Server($socket);

        $http->on('request',function($request,$response){
            if (strtolower($request->getMethod())=='post') {
                $request->on('data',function($data) use($request,$response){
                    $content = $this->handle($request,$response,$data);
                    $response->end($content);
                });
            }
            else
            {
                $content = $this->handle($request,$response);
                $response->end($content);
            }

        });

        $socket->listen(self::PORT);

        self::$loop->run();

    }
}