<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\merchant\OrderIn;
use app\common\model\merchant\OrderRequestLog;

class Pay extends Frontend
{
    protected $noNeedLogin = ['*'];

    /**
     * 支付页面
     */
    public function index()
    {
        $order_id = $this->request->param('order_id');
        if (!$order_id) {
            $this->redirect('/404.html');
        }

        $order = OrderIn::where('order_no', $order_id)->with(['area'])->find();
        if (!$order) {
//            $this->error('订单不存在');
            $this->redirect('/404.html');
        }

        if ($order['status'] != 1) {
//            $this->error('订单已支付');
            $this->redirect('/404.html');
        }

        $request = OrderRequestLog::where('order_no', $order['order_no'])->where('request_type', OrderRequestLog::REQUEST_TYPE_REQUEST)->find();
        $response_data = json_decode($request['response_data'], true);

        $qrcode = 'https://www.baidu.com/img/bd_logo1.png';
        $pix_code = '123456';
        if ($response_data) {
            $pix_code = $response_data['data']['pix_code']??'123456';
        }

        $data = [
            'order_id' => $order['order_no'],
            'amount' => 'R$ ' .$order['amount'],
            'expire_time' => date('Y-m-d H:i:s', time() + 3600),
            'qrcode'=> $qrcode,
            'pix_code' => $pix_code,
        ];

        $this->view->assign('data', $data);
        return $this->view->fetch();
    }
}