<?php

namespace app\common\service\channels;

use app\common\model\merchant\OrderOut;
use fast\Http;
use think\Config;
use think\Log;

/**
 * Fix 支付渠道
 */
class FixPayChannel implements ChannelInterface
{

    public function config()
    {
        return [];
    }

    public function pay($channel, $params): array
    {
        return [
            'status' => 0,
            "msg" => "未开通代收",
        ];
    }

    public function outPay($channel, $params): array
    {
        $mobile = '12345678901';
        $email = 'tikpay@gmail.com';
        $extra = json_decode($params['extra'], true);

        // 如果是电话号码 并且是电话号码没有+55
        if ($extra['pix_type'] == 'PHONE' && strpos($extra['pix_key'], '+55') === false) {
            $extra['pix_key'] = '+55'.$extra['pix_key'];
        }

        $data = [
            'merchantNo' => $channel['mch_id'],
            'merchantOrderNo' => $params['order_no'],
            'description' => '代付',
            'payAmount' => $params['amount'],
            'mobile' => $mobile,
            'email' => $email,
            'bankNumber' =>  $extra['pix_key'],
            'bankCode' => $extra['pix_type'],
            'accountHoldName' => $extra['pix_name'] ?? 'tikpay',
            'notifyUrl' => $this->getNotifyUrl($channel, "outnotify"),
       ];
        $data['sign'] = $this->sign($data, $channel['mch_key']);

        $url = $channel['gateway'] . '/api/payout/order';

        $response = Http::postJson($url, $data);

        Log::write('FixPayChannel outPay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');

        if (!$response || isset($response['msg']) || $response['status'] != 200) {
            return [
                'status' => 0,
                'msg' =>  $response['msg'] ?? $response['message'] ?? '请求失败',
            ];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'msg' => '', // 消息
            'order_id' => $response['data']['platOrderNo'], // 订单号
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
        unset($data['sign']);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            // 为空不参与签名
            if (is_null($v) || $v == '') {
                continue;
            }

            $str .= $k . '=' . $v . '&';
        }

        $sign = md5(md5($str) . $key);
        return $sign;
    }

    public function payNotify($channel, $params): array
    {
        throw new \Exception("未开通代收");
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
        if ($params['orderStatus'] == 'SUCCESS') {
            $status = OrderOut::STATUS_PAID;
        }

        if ($params['orderStatus'] == 'FAILED') {
            $status = OrderOut::STATUS_FAILED;
        }

        if ($status == OrderOut::STATUS_UNPAID) {
            throw new \Exception('未支付');
        }

        return  [
            'order_no' => $params['merchantOrderNo'], // 订单号
            'channel_no' => $params['platOrderNo'], // 渠道订单号
            'pay_date' => date('Y-m-d H:i:s'), // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $params['orderMessage'] ?? '', // 消息
        ];
    }

    public function response(): string
    {
        return "SUCCESS";
    }

    public function getPayInfo($orderIn): array
    {
        return [];
    }

    public function getNotifyType($params): string
    {
       return "";
    }

    public function parseVoucher($params): array
    {
       return [];
    }

    public function getVoucher($channel, $params): array
    {
        return [];
    }

    public function getVoucherUrl($order): string
    {
        // https://pay.paythere.top/getfeedback/
        return   'https://pay.paythere.top/getfeedback/'.$order['order_no'];
    }

}