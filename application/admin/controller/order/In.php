<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\Db;

/**
 * 代付单
 *
 * @icon fa fa-circle-o
 */
class In extends Backend
{

    /**
     * In模型对象
     * @var \app\admin\model\order\In
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\In;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 获取状态列表
     * @return \think\response\Json
     */
    public function status()
    {
        return  json($this->model->getStatusList());
    }

    /**
     * 修改订单状态
     * @param $ids
     */
    public function statusChange($ids = null)
    {
        // 判断是否是post请求
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }

        // 获取post参数
        $params = $this->request->post('row/a');

        // 判断参数是否为空
        if (empty($params) || !isset($params['status'])) {
            $this->error(__('Parameter %s can not be empty', ''));
        }


        Db::startTrans();
        try {
            // todo 这里是修改订单状态的逻辑

        }catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();

        // todo 通知下游

        $this->success();
    }

    /**
     * 通知下游
     */
    public function notify($ids = null)
    {
        // todo 通知下游

        $this->success();
    }

}
