<?php

namespace app\manystore\controller;

use app\common\controller\ManystoreBase;
use app\common\model\Member;
use fast\Random;
use think\Validate;

class Info extends ManystoreBase
{
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Member();
    }
    public function index()
    {
        $row = $this->model->where('id', STORE_ID)->find();
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    // 更新商家信息
    public function update()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('usdt_address', 'ip_white_list', 'password'))
            ));
            unset($v);
            $update = [];
            if (isset($params['password'])) {
                if (!Validate::is($params['password'], "/^[\S]{6,16}$/")) {
                    $this->error(__("Please input correct password"));
                }
                $params['salt'] = Random::alnum();
                $params['password'] = md5($params['password'] . $params['salt']);
                $update['password'] = $params['password'];
                $update['salt'] = $params['salt'];
            }
            $update['usdt_address'] = $params['usdt_address'] ?? '';

            if ($this->model->where('id', STORE_ID)->update($update) !== false) {
                $this->success();
            } else {
                $this->error();
            }
        }
    }
}