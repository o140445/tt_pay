<?php

namespace app\admin\model;

use think\Model;

class MemberWalletModel extends Model
{
    // 表名
    protected $name = 'member_wallet';

    const CHANGE_TYPE_ADD = 1; // 手动增加
    const CHANGE_TYPE_SUB = 2; // 手动减少
    const CHANGE_TYPE_FREEZE = 3; // 手动冻结
    const CHANGE_TYPE_UNFREEZE = 4; // 手动解冻


    const CHANGE_TYPE_PAY_FREEZE = 5; // 代付冻结
    const CHANGE_TYPE_WITHDRAW_FREEZE = 6; // 提现冻结
    const CHANGE_TYPE_CYCLE_FREEZE = 7; // 循环冻结

    // 8 提现解冻
    const CHANGE_TYPE_WITHDRAW_UNFREEZE = 8;

    // 9 提现减少
    const CHANGE_TYPE_WITHDRAW_SUB = 9;

    // 10 代收增加
    const CHANGE_TYPE_PAY_ADD = 10;

    // 11 提成
    const CHANGE_TYPE_COMMISSION_ADD = 11;

    //manual
    const CHANGE_MANUAL_TYPE = [
        self::CHANGE_TYPE_ADD => '增加',
        self::CHANGE_TYPE_SUB => '减少',
        self::CHANGE_TYPE_FREEZE => '冻结',
    ];

    //freeze
    const CHANGE_FREEZE_TYPE = [
        self::CHANGE_TYPE_FREEZE => '手动冻结',
        self::CHANGE_TYPE_PAY_FREEZE => '代付冻结',
        self::CHANGE_TYPE_WITHDRAW_FREEZE => '提现冻结',
        self::CHANGE_TYPE_CYCLE_FREEZE => '循环冻结',
    ];

    public function getChangeType($key='')
    {
        $status = [
            self::CHANGE_TYPE_ADD => '增加',
            self::CHANGE_TYPE_SUB => '减少',
            self::CHANGE_TYPE_FREEZE => '冻结',
        ];

        if (isset($status[$key])) {
            return $status[$key];
        }

        return $status;
    }
}