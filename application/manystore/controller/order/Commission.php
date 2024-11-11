<?php

namespace app\manystore\controller\order;

use app\common\controller\ManystoreBase;
use app\common\model\merchant\Member;
use app\common\model\merchant\Profit;

class Commission extends ManystoreBase
{
    /**
     * In模型对象
     * @var \app\common\model\merchant\Profit
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Profit();
    }

    public function index()
    {
        $id = STORE_ID;
        $member = new Member();
        $member_ids = $member->where('agency_id', $id)->column('id');
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where('member_id', 'in', $member_ids)
                ->order($sort, $order)
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }
}