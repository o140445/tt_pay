<?php

namespace app\common\service\channels;

use app\common\service\HookService;
use fast\Http;
use think\Config;

class AcaciaPayChannel implements ChannelInterface
{
    /**
     * config 配置
     */
    public function config()
    {
        return [
            [
                'name'=>'用户ID',
                'key'=>'userId',
                'value'=>'',
            ],
        ];
    }

    /**
     * pay 支付
     */
    public function pay($channel, $params) : array
    {
        $userId = $this->getExtraConfig($channel, 'userId');
        $data = [
            'userId' => (int)$userId,
            'amount' => (float)$params['amount'],
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'PartnerId' => $channel['mch_id'],
            'AuthKey' => $channel['mch_key'],
        ];
var_dump($data, $headers);die();
        $response =Http::postJson($channel['gateway'], $data, $headers);
        //{
        //    "tx_id": "SHCP4C3C9KOF",
        //    "copia_e_cola": "base64_of_qrcode",
        //    "qrcode": "base64_string_qrcode",
        //    "valor_cobrado": 5,
        //    "method_code": "pix",
        //    "user_id": 10,
        //    "status": "payment.pending"
        //}

        if ($response['status'] != 'payment.pending') {
            return [
                'status' => 0,
                'msg' => '下单失败',
            ];
        }

        $pay_url = Config::get('pay_url') . '/index/pay/index?order_id=' . $params['order_no'];

        // 缓存订单信息$response
        cache('order_in_info_' . $params['order_no'], $response, ['expire' => 600]);

        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => $pay_url, // 支付地址
            'msg' => '下单成功', // 消息
            'order_id' => $response['tx_id'], // 订单号
            'e_no' => '',
            'request_data' => json_encode($data), // 请求数据
            'response_data' => json_encode($response), // 响应数据
        ];
    }

    /**
     * 获取扩展配置
     */
    public function getExtraConfig($channel, $key) {
        $extraConfig = json_decode($channel['extra'], true);
        foreach ($extraConfig as $item) {
            if ($item['key'] == $key) {
                return $item['value'];
            }
        }

        return '';
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
        return "";
    }
}