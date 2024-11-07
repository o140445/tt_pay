<?php

namespace app\admin\controller\order;

use app\admin\model\OrderIn;
use app\common\controller\Backend;
use app\common\service\OrderInService;
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
     * @var \app\admin\model\OrderIn
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\OrderIn;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with(['area'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


    /**
     * 获取状态列表
     * @return \think\response\Json
     */
    public function status()
    {
        return  json($this->model->getStatusList());
    }




    /**
     * 完成订单
     * @param $ids
     */
    public function complete($ids = null)
    {
        // 判断是否是post请求
        if (false === $this->request->isPost()) {
            $this->error('非法请求');
        }

        $orderService = new OrderInService();
        Db::startTrans();
        try {

            $order = $this->model->whereIn('id', $ids)->find();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status != OrderIn::STATUS_UNPAID) {
                throw new \Exception('订单状态不正确');
            }

            $orderService->completeOrder($order, []);

        }catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();

        //  通知下游
        Db::startTrans();
        try {
            $orderService->notifyDownstream($ids);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();
        $this->success("操作成功");

    }

    /**
     * 失败
     */
    public function fail($ids = null)
    {
        // 判断是否是post请求
        if (false === $this->request->isPost()) {
             $this->error('非法请求');
        }

        $orderService = new OrderInService();
        Db::startTrans();
        try {

            $order = $this->model->whereIn('id', $ids)->find();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status != OrderIn::STATUS_UNPAID) {
                throw new \Exception('订单状态不正确');
            }

            $orderService->failOrder($order, ['msg' => '手动失败']);

        }catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();

        //  通知下游
        Db::startTrans();
        try {
            $orderService->notifyDownstream($ids);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();
        $this->success("操作成功");
    }

    /**
     * 通知下游
     */
    public function notify($ids = null)
    {
        // 判断是否是post请求
        if (false === $this->request->isPost()) {
            $this->error('非法请求');
        }
        //  通知下游
        Db::startTrans();
        try {
            $orderService = new OrderInService();
            $orderService->notifyDownstream($ids);
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        Db::commit();
        $this->success("操作成功");
    }


    public function add($ids = null)
    {
        $this->error('非法请求');
    }

    public function edit($ids = null)
    {
        $this->error('非法请求');
    }

    public function del($ids = null)
    {
        $this->error('非法请求');
    }

    public function multi($ids = null)
    {
        $this->error('非法请求');
    }

    public function recyclebin($ids = null)
    {
        $this->error('非法请求');
    }

    public function restore($ids = null)
    {
        $this->error('非法请求');
    }

    public function destroy($ids = null)
    {
        $this->error('非法请求');
    }


}
