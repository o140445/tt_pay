<?php

namespace app\manystore\controller\order;


use app\common\controller\ManystoreBase;
use app\common\model\merchant\OrderIn;
use app\common\service\OrderInService;
use think\Db;

/**
 * 代付单
 *
 * @icon fa fa-circle-o
 */
class In extends ManystoreBase
{

    /**
     * In模型对象
     * @var \app\common\model\merchant\OrderIn
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\merchant\OrderIn;
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
            ->where('member_id', STORE_ID)
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
