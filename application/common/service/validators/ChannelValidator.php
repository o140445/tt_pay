<?php

namespace app\common\service\validators;

use app\common\model\merchant\Channel;
use app\common\service\OrderInService;
use app\common\service\OrderOutService;

class ChannelValidator implements ValidatorInterface
{
    protected $errorMessage;

    public function validate(array $data): bool {
        // 沙盒模式跳过
        if(isset($data['is_sandbox'])) {
            return true;
        }

        if (!isset($data['channel_id']) || empty($data['channel_id'])) {
            $this->errorMessage = "渠道不存在";
            return false;
        }

        $channel = Channel::where('status', OrderInService::STATUS_OPEN)->find($data['channel_id']);
        if (!$channel) {
            $this->errorMessage = "渠道不存在";
            return false;
        }

        // 类型是否开启
        if ($channel->is_in != OrderInService::STATUS_OPEN && $data['type'] == OrderInService::TYPE_IN) {
            $this->errorMessage = "渠道代付未开启";
            return false;
        }

        if ($channel->is_out != OrderInService::STATUS_OPEN && $data['type'] == OrderOutService::TYPE_OUT) {
            $this->errorMessage = "渠道代收未开启";
            return false;
        }

        // 金额检查 最大最小
        if ($data['amount'] < $channel->min_amount && $channel->min_amount != 0) {
            $this->errorMessage = "金额不在范围内" . $channel->min_amount . "-" . $channel->max_amount;
            return false;
        }

        if ($data['amount'] > $channel->max_amount && $channel->max_amount != 0) {
            $this->errorMessage = "金额不在范围内" . $channel->min_amount . "-" . $channel->max_amount;
            return false;
        }

        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}