<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\merchant\Channel;
use app\common\service\OrderManualService;
use app\common\service\OrderOutService;
use think\Config;
use think\Db;

/**
 * 手动订单
 *
 * @icon fa fa-circle-o
 */
class Manual extends Backend
{

    /**
     * Manual模型对象
     * @var \app\common\model\merchant\OrderManual
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\merchant\OrderManual;

    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if (empty($params['amount'])) {
                    $this->error('金额不能为空');
                }
                foreach ($params['extra'] as $key => $value) {
                    if (empty($value)) {
                        $this->error('不能为空');
                    }
                }

                $result = false;
                Db::startTrans();
                try {
                    $orderService = new OrderManualService();
                    $result = $orderService->createOrder($params);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }

                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $channel_id = $this->request->get('channel_id');
        if (empty($channel_id)) {
            $this->error('通道ID不能为空');
        }
        // 获取通道扩展字段
        $data = Channel::find($channel_id);

        if (empty($data)) {
            $this->error('请先添加代付通道');
        }

        $config = Config::get('out_config');
        $area = $data->configArea->name;

        $this->view->assign('area_config', $config[$area]);
        $this->view->assign('channel_id', $channel_id);

        return $this->view->fetch();
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
