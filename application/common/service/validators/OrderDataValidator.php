<?php

namespace app\common\service\validators;

use app\admin\model\OrderIn;

class OrderDataValidator implements ValidatorInterface
{
    protected $errorMessage;

    public function validate(array $data): bool {
        if (!isset($data['merchant_order_no'])) {
            $this->errorMessage = "订单ID是必需的";
            return false;
        }

        if (!isset($data['amount']) || $data['amount'] <= 0) {
            $this->errorMessage = "订单金额是必需的";
            return false;
        }

        // 根据type判断订单类型检查单号是否重复
        if ($data['type'] == "IN") {
            $order = OrderIn::where('member_order_no', $data['merchant_order_no'])->find();
        } else {
//            $order = OrderOut::where('member_order_no', $data['merchant_order_no'])->find();
        }

        if ($order) {
            $this->errorMessage = "订单号已存在";
            return false;
        }


        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}