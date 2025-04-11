<?php

namespace app\manystore\controller\finance;

use app\common\controller\ManystoreBase;
use app\common\model\MemberWallerLog;
use app\common\model\MemberWalletModel;

class wallet extends ManystoreBase
{
    /**
     * @var \app\common\model\MemberWallerLog
     */
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MemberWallerLog();
    }

    public function index()
    {
        $id  = STORE_ID;
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
                ->where('member_id', $id)
                ->order($sort, $order)
                ->paginate($limit);

            $data = $list->items();

            // 设置type_name
            $type = MemberWalletModel::CHANGE_TYPE;
            foreach ($data as $key => $value) {
                $data[$key]['type_name'] = $type[$value['type']] ?? '';
            }


            $result = array("total" => $list->total(), "rows" => $data);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function type()
    {
        return MemberWalletModel::CHANGE_TYPE;
    }
}