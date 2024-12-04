<?php

namespace app\common\service\channels;

use app\common\model\merchant\OrderIn;
use app\common\model\merchant\OrderOut;
use fast\Http;
use think\Config;
use think\Log;

class AcPayChannel implements ChannelInterface
{
    public function config()
    {

        return [
            [
                'name'=>'国家代码',
                'key'=>'country_code',
                'value'=>'',
            ],
        ];
    }

    // getExtValue
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

    public function getNotifyUrl($channel, $type)
    {
        return Config::get('pay_url') . '/api/v1/pay/' . $type . '/code/' . $channel['sign'];
    }

    /**
     * sign
     */
    public function sign($data, $key)
    {
        // 排序
        ksort($data);

        // 拼接
        $str = '';
        foreach ($data as $k => $v) {
            if ($v === '' || $v === null) {
                continue;
            }

            $str .= $k . '=' . $v . '&';
        }

        $str .= 'key=' . $key;
        // 加密
        $str = mb_convert_encoding($str, 'UTF-8', 'auto');
        return md5($str);
    }


    public function pay($channel, $params): array
    {
        $country_code = $this->getExtraConfig($channel, 'country_code');
        $data = [
            'mch_code' => $channel['mch_id'],
            'out_trade_no' => $params['order_no'],
            'amount' => $params['amount'],
            'method' => $country_code,
            'notify_url' => $this->getNotifyUrl($channel, "innotify"),
        ];

        $data['sign'] = $this->sign($data, $channel['mch_key']);

        $url = $channel['gateway'].'/api/gateway/cashIn';

        $response = Http::postJson($url, $data);

        Log::write('AcPayChannel pay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');
        if (!$response || isset($response['msg']) || $response['plat_resp_code'] != 'SUCCESS') {
            return [
                'status' => 0,
                'msg' => $response['msg'] ?? $response['plat_resp_msg'] ?? '支付失败',
            ];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => $response['pay_url'] ?? '', // 支付地址
            'msg' => '', // 消息
            'order_id' => $response['plat_order_no'], // 订单号
            'e_no' => '',
            'request_data' => json_encode($data), // 请求数据
            'response_data' => json_encode($response), // 响应数据
        ];
    }

    public function outPay($channel, $params): array
    {
        $extra = json_decode($params['extra'], true);

        // 如果是电话号码 并且是电话号码没有+55
        if ($extra['pix_type'] == 'PHONE' && strpos($extra['pix_key'], '+55') === false) {
            $extra['pix_key'] = '+55'.$extra['pix_key'];
        }

        // 如果类型是CPF, 去除.-等特殊字符
        if ($extra['pix_type'] == 'CPF') {
            $extra['pix_key'] = preg_replace('/[^0-9]/', '', $extra['pix_key']);
        }


        $country_code = $this->getExtraConfig($channel, 'country_code');
        $data = [
            'mch_code' => $channel['mch_id'],
            'out_trade_no' => $params['order_no'],
            'amount' => $params['amount'],
            'method' => $country_code,
            'bank_name' => $extra['pix_type'],
            'account_no' => $extra['pix_key'],
            'account_name' => $extra['pix_name'],
            'notify_url' => $this->getNotifyUrl($channel, "outnotify"),
        ];

        $data['sign'] = $this->sign($data, $channel['mch_key']);

        $url = $channel['gateway'].'/api/gateway/cashOut';

        $response = Http::postJson($url, $data);

        Log::write('AcPayChannel outPay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');
        if (!$response || isset($response['msg']) || $response['plat_resp_code'] != 'SUCCESS') {
            return [
                'status' => 0,
                'msg' => $response['msg'] ?? $response['plat_resp_msg'] ?? '支付失败',
            ];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'order_id' => $response['plat_order_no'], // 订单号
            'msg' =>  '', // 消息
            'e_no' => '', // 业务订单号息
            'request_data' => json_encode($params), // 请求数据
            'response_data' => json_encode($response), // 响应数据
        ];
    }

    public function payNotify($channel, $params): array
    {

        $data = $params;
        $sign = $data['plat_sign'];
        unset($data['plat_sign']);
        $data['amount'] = number_format($data['amount'], 2, '.', '');
        $mySign = $this->sign($data, $channel['mch_key']);

        if ($sign != $mySign) {
           throw new \Exception("签名错误");
        }

        $status = OrderIn::STATUS_UNPAID;

        if ($data['status'] == 'SUCCESS') {
            $status = OrderIn::STATUS_PAID;
        }else {
            throw new \Exception("未支付");
        }

        return [
            'order_no' => $params['out_trade_no'], // 订单号
            'channel_no' => $params['plat_order_no'], // 渠道订单号
            'amount' => $params['amount'], // 金额
            'pay_date' => '', // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $status == OrderOut::STATUS_PAID ? 'sucesso' : 'canceled', // 消息
        ];
    }

    public function outPayNotify($channel, $params): array
    {
        $data = $params;
        $sign = $data['plat_sign'];
        unset($data['plat_sign']);
        $data['amount'] = number_format($data['amount'], 2, '.', '');
        $mySign = $this->sign($data, $channel['mch_key']);

        if ($sign != $mySign) {
            throw new \Exception("签名错误");
        }

        $status = OrderOut::STATUS_UNPAID;

        if ($data['status'] == 'SUCCESS') {
            $status = OrderOut::STATUS_PAID;
        }

        if ($data['status'] == 'FAIL') {
            $status = OrderOut::STATUS_FAILED;
        }

        return [
            'order_no' => $params['out_trade_no'], // 订单号
            'channel_no' => $params['plat_order_no'], // 渠道订单号
            'amount' => $params['amount'], // 金额
            'pay_date' => '', // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $status == OrderOut::STATUS_PAID ? 'sucesso' : $params['msg']
        ];
    }

    public function response(): string
    {
        return 'SUCCESS';
    }

    public function parseVoucher($params): array
    {
        return  [];
    }

    public function getVoucher($channel, $params): array
    {
       return [];
    }

    public function getPayInfo($orderIn): array
    {
       return  [];
    }

    public function getNotifyType($params): string
    {
        return '';
    }

    public function getVoucherUrl($params): string
    {
        return  '';
    }
}