<?php

namespace app\common\service\channels;

use app\common\service\HookService;

class APayChannel implements ChannelInterface
{
    /**
     * config 配置
     */
    public function config()
    {
        return [
            [
                'name'=>'测试',
                'key'=>'test',
                'value'=>'',
           ],
        ];
    }

    /**
     * pay 支付
     */
    public function pay($channel, $params) : array
    {
        $status = rand(0, 1);
        return [
            'status' => $status, // 状态 1成功 0失败
            'pay_url' => 'http://www.baidu.com', // 支付地址
            'msg' => $status ? '下单成功' : '下单失败', // 消息
            'order_id' => get_order_no('AP'), // 订单号
            'e_no' => '', // 业务订单号
            'request_data' => json_encode($params), // 请求数据
            'response_data' => json_encode($params), // 响应数据
        ];
    }

    /**
     * outPay 出款
     */
    public function outPay($channel, $params) : array
    {
        $status = rand(0, 1);
        return [
            'status' => $status, // 状态 1成功 0失败
            'order_id' => get_order_no('AP'), // 订单号
            'msg' => $status ? '下单成功' : '下单失败', // 消息
            'e_no' => '', // 业务订单号息
            'request_data' => json_encode($params), // 请求数据
            'response_data' => json_encode($params), // 响应数据
        ];
    }

    /**
     * payNotify 支付回调
     */
    public function payNotify($channel, $params) : array
    {
        // todo 签名验证

        // todo 状态判断

        return [
            'order_no' => $params['order_no'], // 订单号
            'channel_no' => $params['txId'], // 渠道订单号
            'amount' => $params['paidAmount'], // 金额
            'pay_date' => date('Y-m-d H:i:s', strtotime($params['paidAt'])), // 支付时间
            'status' => 2, // 状态 2成功 3失败 4退款
            'eno' => $params['endToEndId'], // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => '支付成功', // 消息
        ];
    }

    /**
     * outPayNotify 出款回调
     */
    public function outPayNotify($channel, $params) : array
    {
        // todo 签名验证

        // todo 状态判断

        return [
            'order_no' => $params['order_no'], // 订单号
            'channel_no' => $params['txId'], // 渠道订单号
            'amount' => $params['paidAmount'], // 金额
            'pay_date' => date('Y-m-d H:i:s', strtotime($params['paidAt'])), // 支付时间
            'status' => 1, // 状态 2成功 3失败 4退款
            'eno' => $params['endToEndId'], // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => '支付成功', // 消息
        ];
    }

    /**
     * response 返回
     */
    public function response() : string
    {
        return 'success';
    }

    /**
     * getNotifyType 获取通知类型
     */
    public function getNotifyType($params) : string
    {
        if (isset($params['txId'])) {
            return HookService::NOTIFY_TYPE_IN;
        }

        return HookService::NOTIFY_TYPE_OUT_PAY;
    }
}