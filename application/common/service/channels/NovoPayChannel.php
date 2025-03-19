<?php

namespace app\common\service\channels;

use app\common\model\merchant\OrderIn;
use app\common\model\merchant\OrderOut;
use app\common\model\merchant\OrderRequestLog;
use app\common\service\HookService;
use fast\Http;
use think\Config;
use think\Log;

class NovoPayChannel implements ChannelInterface
{

    protected $header = [
        'Content-Type' => 'application/json',
    ];
    public function config() :array
    {
        return [
            [
                'name'=>'银行名称',
                'key'=>'bankName',
                'value'=>'',
            ],
            [
                'name'=>'CNPJ',
                'key'=>'cnpj',
                'value'=>'',
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

    // setHeader
    public function setHeader($channel) {
        $base64Credentials = base64_encode($channel['mch_id'] . ':' . $channel['mch_key']);
        $this->header['Authorization'] = 'Basic ' . $base64Credentials;
    }

    public function pay($channel, $params): array
    {
        $data = [
            'value' => (int)(round($params['amount'], 2) * 100),
            'expiration' => 3600,
        ];

        $this->setHeader($channel);
        $response = Http::postJson($channel['gateway'].'/payments/new', $data, $this->header);
        Log::write('Novo pay response:'.json_encode($response) . ' data:'.json_encode($data) . ' headers:'.json_encode($this->header), 'info');
    //{"status":400,"message":"An error occurred while trying to generate this payment"}
        if (!$response || isset($response['msg']) || isset($response['message'])) {
            return [
                'status' => 0,
                'msg' => $response['message'] ?? $response['msg'] ?? 'Excepção de pagamento, por favor tente de novo mais tarde',
            ];
        }

        $pay_url = Config::get('pay_url') . '/index/pay/index?order_id=' . $params['order_no'];

        return [
            'status' => 1, // 状态 1成功 0失败
            'pay_url' => $pay_url, // 支付地址
            'msg' => '', // 消息
            'order_id' => $response['id'], // 订单号
            'e_no' => '',
            'request_data' => json_encode($data), // 请求数据
            'response_data' => json_encode($response), // 响应数据
        ];
    }


    /**
     * outPay 出款
     */
    public function outPay($channel, $params) : array
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

        $amount = (string)abs($params['amount']);
        // 到数两位加个小数点
        if (strpos($amount, '.') === false) {
            $amount = $amount * 100;
        }else{
            // 保留两位小数
            $amount = substr($amount, 0, -2) . '.' . substr($amount, -2);
        }

        $data = [
            'value' => $amount,
            'type' => $extra['pix_type'], // PHONE TELEFONE CPF CNPJ
            'key' => $extra['pix_key']
        ];

        $this->setHeader($channel);

        $url = $channel['gateway'].'/payments/withdraw';
        $res = Http::postJson($url, $data, $this->header);

        Log::write('Novo outPay response:'.json_encode($res) . ' data:'.json_encode($data) . ' headers:'.json_encode($this->header), 'info');
//{"status":400,"message":"Invalid value has provided"}
        // 是否是超时 包含 cURL error 7 的错误才是超时
        if (!$res || isset($res['msg']) || isset($res['message'])) {
            return [
                'status' => 0,
                'msg' => $res['message'] ?? $res['msg'] ?? 'Excepção de pagamento, por favor tente de novo mais tarde',
            ];
        }

        //{
        //  "id": "770d0813-ec57-4274-a259-2eb0682ae2b6",
        //  "amount": 1,
        //  "status": "PENDING"
        //}

        return [
            'status' => 1, // 状态 1成功 0失败
            'order_id' => $res['id'] , // 订单号
            'msg' =>  '', // 消息
            'e_no' => '', // 业务订单号
            'request_data' => json_encode($params), // 请求数据
            'response_data' => json_encode($res), // 响应数据
        ];
    }

    /**
     * payNotify 支付回调
     */
    public function payNotify($channel, $params) : array
    {
       //{
        //  "id": "79cc0edd-8cf7-4f8e-961a-6f3651915323",
        //  "value": 10.50,
        //  "status": "COMPLETED",
        //  "type": "IN",
        //  "endToEndId": "E9040088820241221195631832674555",
        //  “payer”: {
        //    “name”: "Joao da silva oliveira",
        //    “document”: "123.456.789-10",
        //    “bank”: "BANCO SANTANDER",
        //  }
        //}

        $status = OrderIn::STATUS_UNPAID;
        if ($params['status'] == 'COMPLETED') {
            $status = OrderIn::STATUS_PAID;
        }

        if ($status == OrderIn::STATUS_UNPAID) {
            throw new \Exception('支付状态错误');
        }

        $amount = $params['value'];

        return [
            'order_no' => "", // 订单号
            'channel_no' => $params['id'], // 渠道订单号
            'amount' => $amount, // 金额
            'pay_date' => '', // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' => $params['endToEndId'], // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $status == OrderOut::STATUS_PAID ? 'sucesso' : 'canceled', // 消息
        ];
    }

    /**
     * outPayNotify 出款回调
     */
    public function outPayNotify($channel, $params) : array
    {
//        {"id":"0a358053f4d94b8da99bd3f35c0ea784","endToEndId":"E22896431202503191309j2Lax5R0Vre","description":"Venda por API","type":"IN","status":"COMPLETED","value":5,"paymentMethod":"PIX","createdAt":"2025-03-19T13:08:23.741Z","updatedAt":"2025-03-19T13:09:59.834Z","payer":{"name":"EDSON MENDES JUNIOR","document":"02462005960"}}

        $status = OrderOut::STATUS_UNPAID;
        if ($params['status'] == 'COMPLETED') {
            $status = OrderOut::STATUS_PAID;
        }
        if ($params['status'] == 'ERROR') {
            $status = OrderOut::STATUS_FAILED;
        }

        if ($status == OrderOut::STATUS_UNPAID) {
            throw new \Exception('支付状态错误');
        }

        return [
            'order_no' =>  '', // 订单号
            'channel_no' => $params['id'], // 渠道订单号
            'pay_date' => '', // 支付时间
            'status' => $status, // 状态 2成功 3失败 4退款
            'e_no' =>  $params['endToEndId'], // 业务订单号
            'data' => json_encode($params), // 数据
            'msg' => $status == OrderOut::STATUS_PAID ? 'sucesso' : 'failed/canceled', // 消息
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
        //{"id":"dff8bc206d71f99c187611e0792ae011","status":"COMPLETED","type":"IN ","value":"5","endToEndId":"E22896431202503181430wuntz4uVZo2","payer":{"name":"EDSON MENDES JUNIOR","document":"02462005960","bank":"PICPAY"}
        // 如果status 包含 payment 是代收， withdraw 是代付 其他是其他
        if (isset($params['type'])) {
            if (strpos($params['type'], 'IN') !== false) {
                return HookService::NOTIFY_TYPE_IN;
            }
            if (strpos($params['type'], 'PIX') !== false) {
                return HookService::NOTIFY_TYPE_OUT_PAY;
            }
        }

        return '';
    }

    public function getPayInfo($order) : array
    {
        $response = OrderRequestLog::where('order_no', $order['order_no'])->where('request_type', OrderRequestLog::REQUEST_TYPE_REQUEST)->find();
        if (! $response) {
            throw new \Exception('支付信息获取失败！');
        }
        $response_data = json_decode($response['response_data'], true);
        //{
        //  "id": "4d2a26f6-e322-4da0-bb16-67912a4bb0a0",
        //  "amount": 0.01,
        //  "expiration": 1800,
        //  "qrcode": "00020101021226890014BR.GOV.BCB.PIX2567qrcode.globalscm.com.br/pix/v2/cob/abd517d39e9243829552de46bd3cf9155204000053039865802BR5909GlobalSCM6009Sao Paulo62070503***63042A31",
        //  "qrcode_image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASQAAAEkCAYAAACG+UzsAAAAAklEQVR4AewaftIAABTCSURBVO3BQW7oSpLAQFLw/a/MectcFSBI9lcPMsL+Ya21PuBirbU+4mKttT7iYq21PuJirbU+4mKttT7iYq21PuJirbU+4mKttT7iYq21PuJirbU+4mKttT7iYq21PuJirbU+4mKttT7iYq21PuKHh1T+UsUTKndUTCpPVEwqJxUnKlPFpDJV3KEyVZyoTBWTylQxqTxRcaIyVUwqU8WJyknFpDJV3KEyVZyo/KWKJy7WWusjLtZa6yMu1lrrI354WcWbVO5QmSqmijtUpooTlanijopJZaqYKk4qJpU7Kn6TylQxqTyhMlVMKlPFicpJxaQyVTxRMalMFScVb1J508Vaa33ExVprfcTFWmt9xA+/TOWOiicqJpU7KqaKE5UTlaniRGWqeFPFpDJVTCpTxUnFpPJfqphUpooTlaniRGWqmFROKiaVqeJNKndU/KaLtdb6iIu11vqIi7XW+ogf/p+rOFGZVKaKSWWquENlqniTyknFHRV3qJxU/JcqJpWTijsqfpPKVPH/ycVaa33ExVprfcTFWmt9xA//41SmijsqnlCZKp5QOak4qThRmSpOVN6kckfFm1SmijtUpopJ5aTijooTlanif9nFWmt9xMVaa33ExVprfcQPv6ziN1WcqEwVk8pUMalMFZPKpDJVTBWTyknFpDJVnKicqEwVJxV3qEwVJyqTylRxonKHylRxh8pUMalMKlPFpHJSMVU8UfElF2ut9REXa631ERdrrfURP7xM5S+pTBX/pYpJZao4qZhUpopJZao4qZhUnlCZKu5QmSomlROVqWJSmSomlROVqWJSuaNiUpkqJpUTlaniROXLLtZa6yMu1lrrIy7WWusjfnio4ktUTlROVJ5QeULlROVE5UTlTRVPVHxZxW+qmFSmiicq/pdcrLXWR1ystdZHXKy11kf88MtUTipOVO6ouENlqjhROamYVO6omFSeqJhUpopJ5UTlCZUnVE5U/ksqU8WbVE5UTiomlaniv3Sx1lofcbHWWh9xsdZaH/HDQyp3VJyoTBV3qPwvqTipmFSmiknlpGJSOamYVKaK36TyX1J5QuWkYqqYVE4qnqg4UZkqftPFWmt9xMVaa33ExVprfcQPD1VMKlPFpDJVnKhM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"
        //}
        return [
            'order_no' => $order['order_no'],
            'qrcode'=> $response_data['qrcode_image'],
            'pix_code' => $response_data['qrcode'],
        ];
    }

    /**
     * 获取凭证
     */
    public function getVoucher($channel, $order) : array
    {
        return  [
            'created_at' => $order['created_at'],
            'e2e' => $order['e_no'],
        ];

    }

    /**
     * 解析凭证
     */
    public function parseVoucher($channel, $order) : array
    {

        //{
        //    "tx_id": "595f42802f4579b58b44c2b0d21abe",
        //    "copia_e_cola": "00020126850014br.gov.bcb.pix2563pix.voluti.com.br/qr/v3/at/a62c3200-8944-48c1-aca8-af816ed0ee925204000053039865802BR5925MEGA_SERVICOS,_TECNOLOGIA6002SP62070503***6304D208",
        //    "qrcode": "iVBORw0KGgoAAAANSUhEUgAAAUAAAAFACAIAAABC8jL9AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAJKkl...etc",
        //    "amount": "5.00",
        //    "method_code": "pix",
        //    "user_id": "123",
        //    "status": "paid",
        //    "payer_name": "付款人姓名",
        //    "ispb": "付款人CPF",
        //    "e2e": "E00416968202411051528kRNvgsChncG",
        //    "created_at": "05/11/2024 12:27",
        //    "updated_at": "05/11/2024 20:28"
        //}
        $payer_name = $this->getExtraConfig($channel, 'bankName');
        $payer_account = $this->getExtraConfig($channel, 'cnpj');
        return [
            'pay_date' => $order['pay_success_date'], // 支付时间
            'payer_name' => $payer_name, // 付款人姓名B.B INVESTIMENT TRADING SERVICOS LTDA
            'payer_account' => $payer_account, // 付款人CPF 57.709.170/0001-67
            'e_no' => $order['e_no'], // 业务订单号
            'type' => 'cnpj', // 业务订单号
        ];
    }

    public function getVoucherUrl($order): string
    {
        return   Config::get('pay_url').'/index/receipt/index?order_id='.$order['order_no'];
    }

    /**
     * 查询订单状态
     */
    public function queryOrder($channel, $channel_no) : array
    {
        $this->setHeader($channel);
        $url = $channel['gateway'].'/transactions/'.$channel_no;

        $res = Http::getJson($url, $this->header);

        Log::write('Novo queryOrder response:'.json_encode($res) . ' headers:'.json_encode($this->header), 'info');

        if (!$res || isset($res['msg']) || isset($res['message'])) {
            return [
                'status' => 0,
                'msg' => $res['message'] ?? $res['msg'] ?? 'Excepção de pagamento, por favor tente de novo mais tarde',
            ];
        }

        return $res;
    }

}