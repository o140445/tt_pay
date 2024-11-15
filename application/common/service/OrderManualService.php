<?php

namespace app\common\service;

use app\common\model\merchant\Channel;
use app\common\model\merchant\OrderManual;

class OrderManualService
{
    public function createOrder($params)
    {
        $channel = Channel::get($params['channel_id']);
        // 创建
        $order = new OrderManual();
        $order->amount = $params['amount'];
        $order->channel_id = $params['channel_id'];
        $order->area_id = $channel->area_id;
        $order->order_no = $this->generateOrderNo();
        $order->status = 1;
        $order->extra = json_encode($params['extra']);
        $order->save();

        //请求支付
        $channelService = new PaymentService($channel->code);
        $channel_res = $channelService->outPay($channel, $order);

        if ($channel_res['status'] == 1) {
            $order->channel_order_no = $channel_res['order_id'] ?? '';
            $order->save();

        } else {

            throw new \Exception($channel_res['msg']);
        }

        return $order;

    }

    public function generateOrderNo()
    {
        return get_order_no('SDDF');
    }
}