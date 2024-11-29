<?php

namespace app\common\service\channels;

use app\common\model\merchant\OrderIn;
use fast\Http;
use think\Config;
use think\Log;

class HeyPayChannel implements ChannelInterface
{
    public function config()
    {
        return [];
    }

    public function pay($channel, $params): array
    {
        $data = [
            'appid' => $channel['mch_id'],
            'amount' => (int)($params['amount'] * 100),
            'order_no' => $params['order_no'],
            'notify_url' => $this->getNotifyUrl($channel, "innotify"),
            'timestamp' => time(),
        ];
        $data['sign'] = $this->sign($data, $channel['mch_key']);

        $url = $channel['gateway'] . '/v1/payin/create';

        $response = Http::postJson($url, $data);
        Log::write('HeyPayChannel pay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');
        if (!$response || $response['code'] != 0 || (isset($response['msg']) && $response['msg'] != 'OK')) {
            return [
                'status' => 0,
                'msg' => $response['msg'] ?? '支付失败',
            ];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => $response['data']['pay_url'] ?? '', // 支付地址
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

    public function sign($data, $key)
    {
        // 移除 sign 键和空值
        $filteredData = array_filter($data, function ($v, $k) {
            return $v !== '' && $k !== 'sign';
        }, ARRAY_FILTER_USE_BOTH);

        // 按照键名 ASCII 升序排序
        ksort($filteredData);

        // 格式化参数并生成签名字符串
        $str = '';
        foreach ($filteredData as $k => $val) {
            $str .= $k . '=' . $val . '&';
        }
        $str = rtrim($str, '&'); // 去除末尾的 &

        // 使用 hash_hmac 生成签名
        return base64_encode(hash_hmac("sha1", $str, $key, true));
    }

    public function outPay($channel, $params): array
    {
       return  [
            'status' => 0,
            "msg" => "未开通代收",
       ];
    }

    public function payNotify($channel, $params): array
    {
        $sign = $params['sign'];
        $params['appid'] = $channel['mch_id'];
        unset($params['sign']);
        $newSign = $this->sign($params, $channel['mch_key']);

        if ($sign != $newSign) {
            throw new \Exception('签名错误');
        }
        $status = OrderIn::STATUS_UNPAID;
        if ($params['status'] == 'SUCCESS') {
            $status = OrderIn::STATUS_PAID;
        }

        if ($params['status'] == 'FAIL' || $params['status'] == 'EXPIRED') {
            $status = OrderIn::STATUS_FAILED;
        }


//        if ($params['status'] == 'RETURN') {
//            $status = OrderIn::STATUS_REFUND;
//        }

        if ($status == OrderIn::STATUS_UNPAID) {
            throw new \Exception('未知状态');
        }


        return [
            'order_no' => $params['merchant_no'], // 订单号
            'channel_no' => $params['plantform_no'], // 渠道订单号
            'amount' => number_format($params['amount'] / 100, 2, '.', ''), // 金额
            'pay_date' => "", // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => '', // 消息
        ];
    }

    public function outPayNotify($channel, $params): array
    {
        throw new \Exception('Method not implemented.');
    }

    public function getNotifyType($params): string
    {
       return  '';
    }

    public function getPayInfo($orderIn): array
    {
        return [];
    }

    public function getVoucher($channel, $params): array
    {
        return [];
    }

    public function parseVoucher($params): array
    {
        return [];
    }

    public function response(): string
    {
        return  "SUCCESS";
    }

    public function getVoucherUrl($params): string
    {
        return  "";
    }
}