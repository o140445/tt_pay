<?php

namespace app\manystore\controller;

use app\common\controller\ManystoreBase;
use app\common\model\merchant\Member;
use PragmaRX\Google2FA\Google2FA;

class Security extends ManystoreBase
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
        // 如果没有 google_token 则生成一个
        if (!$row['google_token']) {
            $row['google_token'] = $this->model->generateGoogleToken();
            $this->model->where('id', STORE_ID)->update(['google_token' => $row['google_token']]);
        }

        // 如果未绑定google验证器，生成一个二维码
        $google_qrcode = '';
        if (!$row['is_bind_google']) {
            $google_qrcode = $this->model->generateGoogleQrcode($row->username, $row['google_token']);
        }

        $this->view->assign("google_qrcode", $google_qrcode);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 绑定google验证器
     */
    public function bindgoogle()
    {

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('google_code'))
            ));

            unset($v);

            $row = $this->model->where('id', STORE_ID)->find();
            if (!$row) {
                $this->error(__('Invalid data'));
            }

            if ($this->verifyGoogle($params['google_code'], $row['google_token'])) {
                $update = [
                    'is_bind_google' => 1,
                    'is_verify_google' => 1,
                ];
                if ($this->model->where('id', STORE_ID)->update($update) !== false) {
                    $this->success();
                } else {
                    $this->error();
                }
            } else {
                $this->error(__('Invalid google code'));
            }
        }
    }

    /**
     * 验证google验证器
     */
    public function verifyGoogle($code, $google_token)
    {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($google_token, $code);
    }

    /**
     * 解google验证器
     */
    public function unbindgoogle()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key(
                $params,
                array_flip(array('google_code'))
            ));

            unset($v);

            $row = $this->model->where('id', STORE_ID)->find();
            if (!$row) {
                $this->error(__('Invalid data'));
            }

            if ($this->verifyGoogle($params['google_code'], $row['google_token'])) {
                $update = ['is_bind_google' => 0];
                if ($this->model->where('id', STORE_ID)->update($update) !== false) {
                    $this->success();
                } else {
                    $this->error();
                }
            } else {
                $this->error(__('Invalid google code'));
            }
        }
    }
}