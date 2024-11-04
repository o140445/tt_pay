<?php

namespace app\common\service\validators;

use app\admin\model\Member;
use app\common\service\OrderService;
use app\common\service\SignService;

class SignatureValidator implements ValidatorInterface
{
    protected $errorMessage;

    public function validate(array $data): bool {
        if (!isset($data['sign'])) {
            $this->errorMessage = "参数签名是必需的";
            return false;
        }

        $secret = Member::where('status', OrderService::STATUS_OPEN)->find($data['merchant_id'])->api_key;

        $signService = new SignService();
        $signData = [
            'merchant_id' => $data['merchant_id'],
            'merchant_order_no' => $data['merchant_order_no'],
            'amount' => $data['amount'],
            'product_id' => $data['product_id'],
            'notify_url' => $data['notify_url'],
            'nonce' => $data['notice'],
            'sign' => $data['sign'],
        ];
        if (!$signService->checkSign($signData, $secret)) {
            $this->errorMessage = "签名验证失败";
            return false;
        }
        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }

}