<?php

namespace app\index\controller;

use app\common\controller\Frontend;
class Receipt extends Frontend
{
    protected $noNeedLogin = ['*'];

    public function index()
    {
//        $order_id = $this->request->param('order_id');
//        if (!$order_id) {
//            $this->redirect('/404.html');
//        }
//
//        $order = OrderIn::where('order_no', $order_id)->with(['area'])->find();
//        if (!$order) {
//            $this->redirect('/404.html');
//        }
//
//        if ($order['status'] != 1) {
//            $this->redirect('/404.html');
//        }
//
//        $request = OrderRequestLog::where('order_no', $order['order_no'])->where('request_type', OrderRequestLog::REQUEST_TYPE_REQUEST)->find();
//        $response_data = json_decode($request['response_data'], true);
//
//        $qrcode = 'https://www.baidu.com/img/bd_logo1.png';
//        $pix_code = '123456';
//        if ($response_data) {
//            $pix_code = $response_data['data']['pix_code']??'123456';
//        }
//
//    <p class="info"><strong>Hora do Pagamento:</strong> {$data.payment_time}</p>
//    <p class="info"><strong>Número do Pedido (e_no):</strong> {$data.e_no}</p>
//    <p class="info"><strong>Conta do Pagador:</strong> {$data.payer_account}</p>
//    <p class="info"><strong>Nome do Pagador:</strong> {$data.payer_name}</p>
//    <p class="info"><strong>Conta do Recebedor:</strong> {$data.payee_account}</p>
//    <p class="info"><strong>Nome do Recebedor:</strong> {$data.payee_name}</p>
        $data = [
            'payment_time' => '2021-09-01 12:00:00',
            'e_no' => '123456',
            'payer_account' => '123456',
            'payer_name' => 'John Doe',
            'payee_account' => '123456',
            'payee_name' => 'Jane Doe',
            'amount' => 'R$ 100.00',
        ];
//
        $this->view->assign('data', $data);
        return $this->view->fetch();
    }
}