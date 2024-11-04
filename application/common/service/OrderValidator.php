<?php

namespace app\common\service;

use app\common\service\validators\ChannelValidator;
use app\common\service\validators\MemberValidator;
use app\common\service\validators\ProjectValidator;
use app\common\service\validators\SignatureValidator;
use app\common\service\validators\OrderDataValidator;

class OrderValidator
{
    protected $validators = [];
    protected $errors = [];

    public function __construct() {
        $this->validators = [
            new MemberValidator(), // 会员验证器
            //new SignatureValidator(), // 签名验证器
            new OrderDataValidator(), // 订单验证器
            new ProjectValidator(), // 项目验证器
            new ChannelValidator() // 渠道验证器
        ];
    }

    public function validateOrder(array $orderData): bool {
        foreach ($this->validators as $validator) {
            if (!$validator->validate($orderData)) {
                $this->errors[] = $validator->getErrorMessage();
                break;
            }
        }

        return empty($this->errors); // 若无错误则返回 true
    }

    public function getErrors(): array {
        return $this->errors;
    }
}