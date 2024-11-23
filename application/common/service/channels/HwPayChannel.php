<?php

namespace app\common\service\channels;

use app\common\model\merchant\OrderIn;
use app\common\model\merchant\OrderOut;
use app\common\service\SignService;
use fast\Http;
use think\Config;
use think\Log;

class HwPayChannel implements ChannelInterface
{
    public function config()
    {
        return [
            [
                'name' => '通道ID',
                'key' => 'productId',
                'value' => '',
            ],
        ];
    }

    public function pay($channel, $params): array
    {
        $productId = $this->getExtraConfig($channel, 'productId');
        $data = [
            'amount' => (float)$params['amount'],
            'merchant_id' => $channel['mch_id'],
            'product_id' => $productId,
            'merchant_order_no' => $params['order_no'],
            'nonce' => get_random_string(),
            'notify_url' => $this->getNotifyUrl($channel, "innotify"),
        ];

        $data['sign'] = $this->sign($data, $channel['mch_key']);

        $url = $channel['gateway'] . 'api/v1/pay/in';
        $response = Http::postJson($url, $data);
        Log::write('HwPayChannel pay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');
        if (!$response || $response['code'] == 0) {
            return [
                'status' => 0,
                'msg' =>  $response['msg'] ?? '请求失败',
            ];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => $response['data']['pay_url'], // 支付地址
            'msg' => '', // 消息
            'order_id' => $response['data']['order_no'], // 订单号
            'e_no' => '',
            'request_data' => json_encode($data), // 请求数据
            'response_data' => json_encode($response), // 响应数据
        ];
    }

    public function getNotifyUrl($channel, $type)
    {
        return Config::get('pay_url') . '/api/v1/pay/' . $type . '/code/' . $channel['sign'];
    }


    public function outPay($channel, $params): array
    {
        $productId = $this->getExtraConfig($channel, 'productId');
        $data = [
            'amount' => (float)$params['amount'],
            'merchant_id' => $channel['mch_id'],
            'product_id' => $productId,
            'merchant_order_no' => $params['order_no'],
            'nonce' => get_random_string(),
            'extra' => json_decode($params['extra'], true),
            'notify_url' => $this->getNotifyUrl($channel, "outnotify"),
        ];

        $data['sign'] = $this->sign($data, $channel['mch_key']);


        $url = $channel['gateway'] . '/api/v1/pay/out';
        $response = Http::postJson($url, $data);
        Log::write('HwPayChannel outPay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');

        if (!$response || $response['code'] == 0) {
            return [
                'status' => 0,
                'msg' =>  $response['msg'] ?? '请求失败',
            ];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'order_id' => $response['data']['order_no'], // 订单号
            'msg' => '', // 消息
            'e_no' => '', // 业务订单号息
            'request_data' => json_encode($params), // 请求数据
            'response_data' => json_encode($response['data']), // 响应数据
        ];
    }

    public function response(): string
    {
        return "success";
    }

    public function payNotify($channel, $params): array
    {
        // 解析签名
        $sign = $params['sign'];
        unset($params['sign']);
        $newSign = $this->sign($params, $channel['mch_key']);
        if ($sign != $newSign) {
            throw new \Exception('签名错误');
        }

        $status = OrderIn::STATUS_UNPAID;
        if ($params['status'] == OrderIn::STATUS_PAID) {
            $status = OrderIn::STATUS_PAID;
        }
        if ($params['status'] == OrderIn::STATUS_FAILED) {
            $status = OrderIn::STATUS_FAILED;
        }

        return [
            'order_no' => $params['merchant_order_no'], // 订单号
            'channel_no' => '', // 渠道订单号
            'amount' => $params['amount'], // 金额
            'pay_date' => $params['pay_success_date'], // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $params['msg'] ?? '', // 消息
        ];
    }

    public function outPayNotify($channel, $params): array
    {
        $sign = $params['sign'];
        unset($params['sign']);

        $newSign = $this->sign($params, $channel['mch_key']);
        if ($sign != $newSign) {
            throw new \Exception('签名错误');
        }

        $status = OrderOut::STATUS_UNPAID;
        if ($params['status'] == OrderOut::STATUS_PAID) {
            $status = OrderOut::STATUS_PAID;
        }

        if ($params['status'] == OrderOut::STATUS_FAILED) {
            $status = OrderOut::STATUS_FAILED;
        }

        return [
            'order_no' => $params['merchant_order_no'], // 订单号
            'channel_no' => '', // 渠道订单号
            'pay_date' => $params['pay_success_date'], // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $params['msg'] ?? '', // 消息
        ];


    }

    public function getNotifyType($params): string
    {
        return  "";
    }

    public function getPayInfo($orderIn): array
    {
        return [];
    }

    public function parseVoucher($params): array
    {
        // TODO: Implement parseVoucher() method.
        return [];
    }

    public function getVoucher($channel, $params): array
    {
        return [];
    }

    public function sign($data, $key)
    {
        $signService = new SignService();
        return $signService->makeSign($data, $key);
    }

    /**
     * 获取扩展配置
     */
    public function getExtraConfig($channel, $key)
    {
        $extraConfig = json_decode($channel['extra'], true);
        foreach ($extraConfig as $item) {
            if ($item['key'] == $key) {
                return $item['value'];
            }
        }

        return '';

    }
}