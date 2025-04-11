<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\OrderIn;
use app\common\service\PaymentService;
use fast\Http;
use think\Cache;
use think\Config;
use think\Log;

class Pay extends Frontend
{
    protected $noNeedLogin = ['*'];

    protected $layout = 'default';

    /**
     * 支付页面
     */
    public function index()
    {
        $order_id = $this->request->param('order_id');

        if (!$order_id) {
            $this->redirect('/404.html');
        }

        $order = OrderIn::with('channel')->where('order_no', $order_id)->where('status', OrderIn::STATUS_UNPAID)->find();
        if (!$order) {
//            $this->error('订单不存在');
            $this->redirect('/404.html');
        }


        $key =  'order_in_info_' . $order['order_no'];
        $response = Cache::get($key);
        if (!$response) {
            $channelService = new  PaymentService($order->channel->code);
            $response_data = $channelService->getPayInfo($order);
        }else{
            $response_data = $response;
        }

        $data = [
            'order_id' => $order['order_no'],
            'amount' => 'R$ ' . number_format($order['amount'], 2, '.', ''),
            'expire_time' => date('d/m/y H:i', strtotime($order['create_time']) + 3600),
            'qrcode'=> $response_data['qrcode'],
            'pix_code' => $response_data['pix_code'],
        ];

        $this->view->assign('data', $data);
        return $this->view->fetch();
    }

    /**
     * 拉入cdn黑名单
     */
    protected function black($ip)
    {
        //curl 'https://wafx.sucuri.net/api?v2' \
        //--data 'k=API_KEY' \
        //--data 's=API_SECRET' \
        //--data 'a=blocklist_ip' \
        //--data 'ip=IP_ADDRESS' \
        //--data 'duration=(time in seconds)'
        // 永久拉入黑名单

        $url = 'https://wafx.sucuri.net/api?v2';
        $data = [
            'k' =>  Config::get('sucuri.api_key'),
            's' =>  Config::get('sucuri.api_secret'),
            'a' => 'blocklist_ip',
            'ip' => $ip,
            'duration' => 86400,
        ];

        $res = Http::post($url, $data);
        // 写入日志
        Log::write('拉入cdn黑名单', $res);
    }

}