<?php

namespace app\common\service\channels;

class APayChannel implements ChannelInterface
{
    /**
     * config 配置
     */
    public function config()
    {
        return [
            [
                'name'=>'商户ID',
                'key'=>'merchant_id',
                'value'=>'',
           ],
            [
                'name'=>'商户密钥',
                'key'=>'merchant_key',
                'value'=>'',
            ],
            [
                'name'=>'支付地址',
                'key'=>'pay_url',
                'value'=>'',
            ]
        ];
    }

    /**
     * pay 支付
     */
    public function pay($channel, $params) : array
    {
        return [
            'status' => 0, // 状态 1成功 0失败
            'pay_url' => 'http://www.baidu.com', // 支付地址
            'msg' => '下单失败xxx', // 消息
            'order_id' => '123456', // 订单号
            'e_no' => '123456', // 业务订单号
        ];
    }

    /**
     * outPay 出款
     */
    public function outPay($channel, $params) : array
    {
        return [
            'status' => 1, // 状态 1成功 0失败
            'order_id' => '123456', // 订单号
            'msg' => '下单成功', // 消息
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
            'status' => 1, // 状态 2成功 3失败 4退款
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
}