<?php

namespace app\common\service\channels;

class TestPayChannel extends ChannelBase implements ChannelInterface
{
    public function config(): array
    {
        return [
            [
                'name'=>'CNPJ',
                'key'=>'cnpj',
                'value'=>'',
            ]
        ];
    }

    public function pay($channel, $params): array
    {
        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => 'https://www.baidu.com', // 支付地址
            'msg' => '', // 消息
            'order_id' => get_order_no('CH'), // 订单号
            'e_no' => '',
            'request_data' => '', // 请求数据
            'response_data' => '', // 响应数据
        ];
    }

    public function outPay($channel, $params): array
    {
        return [
            'status' => 1, // 状态 1成功 0失败
            'order_id' => get_order_no('CHO') , // 订单号
            'msg' =>  '', // 消息
            'e_no' => '', // 业务订单号
            'request_data' => '', // 请求数据
            'response_data' => '', // 响应数据
        ];
    }

    public function getPayInfo($orderIn): array
    {
        // TODO: Implement getPayInfo() method.
    }

    public function payNotify($channel, $params): array
    {
        // TODO: Implement payNotify() method.
    }

    public function outPayNotify($channel, $params): array
    {
        // TODO: Implement outPayNotify() method.
    }

    public function getNotifyType($params): string
    {
        // TODO: Implement getNotifyType() method.
    }

    public function getVoucherUrl($params): string
    {
        // TODO: Implement getVoucherUrl() method.
    }

    public function parseVoucher($channel, $params): array
    {
        // TODO: Implement parseVoucher() method.
    }

    public function response(): string
    {
        // TODO: Implement response() method.
    }
}