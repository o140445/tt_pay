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

    //manual
    const CHANGE_MANUAL_TYPE = [
        self::CHANGE_TYPE_ADD => '增加',
        self::CHANGE_TYPE_SUB => '减少',
        self::CHANGE_TYPE_FREEZE => '冻结',
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