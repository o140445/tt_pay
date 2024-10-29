<?php

namespace app\admin\controller\payment;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class Channel extends Backend
{
    /**
     * @var \app\admin\model\Channel
     */
    protected $model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Channel;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a",'strip_tags');
            $params['sign'] = $this->getSign();

            if ($params) {
                $this->model->create($params);
                $this->success();
            }
            $this->error();
        }

        return parent::add();
    }


    protected  function getSign()
    {

        $string = get_random_string(4);

        // 检查是否已经存在
        $count = $this->model->where('sign', $string)->count();
        if ($count > 0) {
            return $this->getSign();
        }

        return $string;

    }


}