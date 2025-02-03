<?php

namespace app\common\service\channels;

use app\common\model\merchant\OrderIn;
use app\common\model\merchant\OrderOut;
use app\common\service\OrderInService;
use app\common\service\OrderOutService;
use fast\Http;
use think\Config;
use think\Log;

class BPPayChannel implements ChannelInterface
{
    public function config()
    {
        return [
            [
                'name'=>'公钥',
                'key'=>'publicKey',
                'value'=>'',
            ],
            [
                'name'=>'国家代码',
                'key'=>'countryCode',
                'value'=>'PHL',
            ],
            [
                'name'=>'货币代码',
                'key'=>'currencyCode',
                'value'=>'PHP',
            ],
            [
                'name'=>'代收支付类型',
                'key'=>'paymentType',
                'value'=>'902410172001',
            ],
            [
                'name'=>'代付支付类型',
                'key'=>'outPaymentType',
                'value'=>'902410175001',
            ],
            [
                'name' => '银行代码',
                'key' => 'bankCode',
                'value' => 'GCASH'
            ]
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
     * 金额处理
     */
    public function amountHandle($amount) {
        return number_format($amount, 2, '.', '');
    }

    public function getNotifyUrl($channel, $type)
    {
        return Config::get('pay_url') . '/api/v1/pay/' . $type . '/code/' . $channel['sign'];
    }

    public function pay($channel, $params): array
    {
        $data = [
            'merchantNo' => $channel['mch_id'],
            'merchantOrderNo' => $params['order_no'],
            'countryCode' => $this->getExtraConfig($channel, 'countryCode'),
            'currencyCode' => $this->getExtraConfig($channel, 'currencyCode'),
            'paymentType' => $this->getExtraConfig($channel, 'paymentType'),
            'paymentAmount' => $this->amountHandle($params['amount']),
            'goods' => 'goods',
            'notifyUrl' => $this->getNotifyUrl($channel, 'innotify'),
        ];

        //mch_key
        $key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
        $key .= wordwrap($channel->mch_key, 64, "\n", true);
        $key .= PHP_EOL."-----END PRIVATE KEY-----";

        $data['sign'] = $this->sign($data, $key);

        $url = $channel['gateway'] .'/api/v2/payment/order/create';
        $response = Http::postJson($url, $data, []);
        Log::write('BPPayChannel pay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');

        if ($response['code'] != 200) {
            return ['status' => 0, 'msg' => $response['message'] ?? '支付失败'];
        }


        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => $response['data']['paymentUrl'], // 支付地址
            'msg' => '', // 消息
            'order_id' => $response['data']['orderNo'], // 订单号
            'e_no' => '',
            'request_data' => json_encode($data), // 请求数据
            'response_data' => json_encode($response['data']), // 响应数据
        ];
    }

    /**
     * 签名
     */
    public function sign($data, $key)
    {
        // 排序
        ksort($data);

        // 拼接
        $str = '';
        foreach ($data as $k => $v) {
            if ($v === '' || $v === null ) {
                continue;
            }

            $str .= $k . '=' . $v . '&';
        }

        // 移除最后一个&
        $str = substr($str, 0, -1);

        $key = openssl_get_privatekey($key);
        openssl_sign($str, $sign_info, $key, OPENSSL_ALGO_MD5);
        $sign = base64_encode($sign_info);
        return $sign;
    }
    public function outPay($channel, $params): array
    {
        $extra = json_decode($params['extra'], true);

        //bankAccount^7280101686|bankCode^GCASH
        $extendedParams = "bankAccount^" . $extra['bankAccount'] . "|bankCode^" . $this->getExtraConfig($channel, 'bankCode');

        $data = [
            'merchantNo' => $channel['mch_id'],
            'merchantOrderNo' => $params['order_no'],
            'countryCode' => $this->getExtraConfig($channel, 'countryCode'),
            'currencyCode' => $this->getExtraConfig($channel, 'currencyCode'),
            'transferType' => $this->getExtraConfig($channel, 'outPaymentType'),
            'transferAmount' => $this->amountHandle($params['amount']),
            'feeDeduction' => 1,
            'remark' => 'Ilipat',
            'notifyUrl' => $this->getNotifyUrl($channel, 'outnotify'),
            'extendedParams' => $extendedParams,
        ];

        //mch_key
        $key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
        $key .= wordwrap($channel->mch_key, 64, "\n", true);
        $key .= PHP_EOL."-----END PRIVATE KEY-----";

        $data['sign'] = $this->sign($data, $key);

        $url = $channel['gateway'] . '/api/v2/transfer/order/create';

        $response = Http::postJson($url, $data, []);
        Log::write('BPPayChannel outPay response:' . json_encode($response) . ' data:' . json_encode($data), 'info');

        if ($response['code'] != 200) {
            Log::write('BPPayChannel outPay response:' . json_encode($response) . ' data:' . json_encode($data), 'error');
            return ['status' => 0, 'msg' => $response['message'] ?? '支付失败'];
        }

        return [
            'status' => 1, // 状态 1成功 0失败
            'order_id' => $response['data']['orderNo'] ?? '', // 订单号
            'msg' =>  '', // 消息
            'e_no' =>  '', // 业务订单号
            'request_data' => json_encode($params), // 请求数据
            'response_data' => json_encode($response['data']), // 响应数据
        ];

    }

    public function payNotify($channel, $params): array
    {
        // array (
        //  'orderNo' => '6012220116000004',
        //  'orderTime' => '2022-01-16 16:05:45',
        //  'orderAmount' => '100.00',
        //  'countryCode' => 'THA',
        //  'sign' => 'Cmkdx85RlWUgHTt27E21GyUg8yT74AW3ZujFghV4KmaSd93LCT4aDz0j/aUWf2jY2ZbMYOqbwnZZ7N63doGJuATdWzmmNSi6gRVnnCCvR5h12syuv8ab+j++NQbE2wc/wicqGF1c0D5eUwrEm414JC+aIq/ESe0/hWJ8wbaq87s=',
        //  'paymentTime' => '2022-01-16 16:05:58',
        //  'merchantOrderNo' => '1642323938',
        //  'paymentAmount' => '100.00',
        //  'currencyCode' => 'THB',
        //  'paymentStatus' => 'SUCCESS',
        //  'returnedParams' => '回传参数',
        //  'merchantNo' => '3018220107001',
        //)
        $key = "-----BEGIN PUBLIC KEY-----".PHP_EOL;
        $key .= wordwrap($this->getExtraConfig($channel, 'publicKey'), 64, "\n", true);
        $key .= PHP_EOL."-----END PUBLIC KEY-----";

        //验证签名
        if (!$this->parseSign($params, $key)) {
            throw new \Exception('签名错误');
        }

        $status = $params['paymentStatus'] == 'SUCCESS' ? OrderIn::STATUS_PAID : OrderIn::STATUS_UNPAID;

        return [
            'order_no' => $params['merchantOrderNo'], // 订单号
            'channel_no' => $params['orderNo'], // 渠道订单号
            'amount' => $params['paymentAmount'], // 金额
            'pay_date' => $params['paymentTime'], // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => '', // 消息
        ];
    }

    public function outPayNotify($channel, $params): array
    {
        // array (
        //  'orderNo' => '7015220116000002',
        //  'orderTime' => '2022-01-16 19:51:48',
        //  'transferStatus' => 'SUCCESS',
        //  'transferTime' => '2022-01-16 22:01:25',
        //  'countryCode' => 'THA',
        //  'orderAmout' => '15.00',
        //  'transferAmount' => '15.00',
        //  'sign' => 'V9Rv2UkjP5lwT/EwONd2//kXq8JqdvnDy/mbjhxwI10JV4syw64jdQZrJ1Sm4ts7njSNAbgdlzelkm3iBUqAorqLcoQrCjrgH567A7ZnFbeoSauqxw4bpK2mR9Jvuv/VzpfFgdEoAmp9KIlEAFHzTu4att+7x4jutTvaRV5SUmY=',
        //  'merchantOrderNo' => '1642337500',
        //  'currencyCode' => 'THB',
        //  'merchantNo' => '3018220107001',
        //)
        $key = "-----BEGIN PUBLIC KEY-----".PHP_EOL;
        $key .= wordwrap($this->getExtraConfig($channel, 'publicKey'), 64, "\n", true);
        $key .= PHP_EOL."-----END PUBLIC KEY-----";

        if (!$this->parseSign($params, $key)) {
            throw new \Exception('签名错误');
        }

        //PROCESSING 处理中FAILED 失败SUCCESS 成功
        $status = OrderOut::STATUS_UNPAID;
        if ($params['transferStatus'] == 'SUCCESS') {
            $status = OrderOut::STATUS_PAID;
        }

        if ($params['transferStatus'] == 'FAILED') {
            $status = OrderOut::STATUS_FAILED;
        }

        return [
            'order_no' => $params['merchantOrderNo'], // 订单号
            'channel_no' => $params['orderNo'], // 渠道订单号
            'amount' => $params['transferAmount'], // 金额
            'pay_date' => $params['transferTime'], // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => '', // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $status == OrderOut::STATUS_PAID ? '成功' : '失败', // 消息
        ];
    }



    /**
     * 解析签名
     */
    public function parseSign($data, $key)
    {
        $sign = $data['sign'];
        unset($data['sign']);
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

        // 移除最后一个&
        $str = substr($str, 0, -1);

        $pay_public_key = openssl_get_publickey($key);
        $result  = openssl_verify($str,base64_decode($sign),$pay_public_key,OPENSSL_ALGO_MD5);
        return $result;
    }




    public function getPayInfo($orderIn): array
    {
        // TODO: Implement getPayInfo() method.
    }

    public function response(): string
    {
        return "SUCCESS";
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

    public function getVoucher($channel, $params): array
    {
        // TODO: Implement getVoucher() method.
    }
}