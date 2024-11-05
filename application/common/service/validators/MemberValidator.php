<?php

namespace app\common\service\validators;

use app\admin\model\Member;
use app\common\service\MemberWalletService;
use app\common\service\OrderInService;

class MemberValidator implements ValidatorInterface
{
    protected $errorMessage;

    public function validate(array $data): bool {
        if (!isset($data['merchant_id'])) {
            $this->errorMessage = "商户ID是必需的";
            return false;
        }

        $member = Member::where('status', OrderInService::STATUS_OPEN)->find($data['merchant_id']);
        if (!$member) {
            $this->errorMessage = "商户不存在";
            return false;
        }

        //对接类型检查
        if ($member->docking_type != Member::DOCKING_TYPE_API) {
            $this->errorMessage = "商户类型不支持API对接";
            return false;
        }

        // ip白名单检查
        if ($member->ip_white_list) {
            $ip = request()->ip();
            if (!in_array($ip, explode(',', $member->ip_whitelist))) {
                $this->errorMessage = "IP不在白名单中";
                return false;
            }
        }

        // 余额检查 out
        if ($data['type'] == "OUT") {
            $memberWallet = $member->wallet;
            if ($memberWallet->balance < $data['amount']) {
                $this->errorMessage = "余额不足";
                return false;
            }
        }


        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}