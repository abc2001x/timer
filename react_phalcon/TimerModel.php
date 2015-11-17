<?php
/**
 * 与open_platform表映射
 * User: wangjiankun
 * Date: 15/8/29
 * Time: 11:42
 */

namespace ReactQue;

use Phalcon\Mvc\Model;

class TimerModel extends Model {

    public $id;
    public $ctime;
    public $class;
    public $method;
    public $params;
    public $exec_time;
    // public $is_consume;

    public function beforeCreate() {
        $this->ctime = time();
        $this->is_consume = 0;
        
    }

    public function getSource() {
        return "timer_task";
    }
    

    public static function getTasks($page,$perpage=10,$sort=null){
        $sortFields = [
                    'exec_time'=>'task.exec_time',
                    'id'=>'task.id',
                    ];
        $sortValue = ['up'=>'ASC','down'=>'DESC'];

        $o = new self();
        $builder = $o->modelsManager->createBuilder()
        ->columns(['task.*'])
        ->from(['task'=>'\\ReactQue\\TimerModel']);

        if ($sort) {
            $sortArr=[];
            foreach ($sort as $k=>$v) {
                if (array_key_exists($k,$sortFields) && array_key_exists($v,$sortValue)) {
                    $sortArr[] = $sortFields[$k].' '.$sortValue[$v];
                }
            }
            
            if ($sortArr) {
                $builder->orderBy($sortArr);
            }
            
        }

        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(
            array(
                "builder" => $builder,
                "limit"   => $perpage,
                "page"    => $page
            )
        );

        return $paginator->getPaginate();

    }

    public static function deleteTask($id){
        $t = self::findFirst('id='.$id);
        $t->delete();
    }

}
