<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\merchant\OrderNotifyLog;

class Notify  extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new OrderNotifyLog();
        $this->view->assign("notifyStatusList", $this->model->getNotifyStatusAttr());
    }

    public function index($ids = null)
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

        $order_no = $this->request->get('order_no');

        if (!$order_no) {
            $this->error('非法请求');
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->where('order_no', $order_no)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
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

    public function multi($ids = "")
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