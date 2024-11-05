<?php

namespace app\common\service\validators;

use app\admin\model\Channel;
use app\common\service\OrderInService;

class ChannelValidator implements ValidatorInterface
{
    protected $errorMessage;

    public function validate(array $data): bool {
        if (!isset($data['channel_id']) || empty($data['channel_id'])) {
            $this->errorMessage = "渠道不存在";
            return false;
        }

        $channel = Channel::where('status', OrderInService::STATUS_OPEN)->find($data['channel_id']);
        if (!$channel) {
            $this->errorMessage = "渠道不存在";
            return false;
        }

        // 金额检查 最大最小
        if ($data['amount'] < $channel->min_amount || $data['amount'] > $channel->max_amount) {
            $this->errorMessage = "金额不在范围内" . $channel->min_amount . "-" . $channel->max_amount;
            return false;
        }

        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}