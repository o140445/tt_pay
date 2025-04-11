<?php

namespace app\admin\controller\finance;

use app\common\controller\Backend;

/**
 * 利润报管理
 *
 * @icon fa fa-circle-o
 */
class Profit extends Backend
{

    /**
     * Profit模型对象
     * @var \app\common\model\Profit
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\Profit;

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

    public function add()
    {
        $this->error('请求错误');
    }

    public function edit($ids = null)
    {
        $this->error('请求错误');
    }

    public function del($ids = "")
    {
        $this->error('请求错误');
    }

    public function multi($ids = "")
    {
        $this->error('请求错误');
    }

    public function destroy($ids = "")
    {
        $this->error('请求错误');
    }


    public function restore($ids = "")
    {
        $this->error('请求错误');
    }

    public function recyclebin()
    {
        $this->error('请求错误');
    }
}
