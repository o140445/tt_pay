<?php

namespace app\common\model;

use think\Model;

class MemberWalletFreezeModel extends  Model
{
    protected $name = 'member_wallet_freeze';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
        });
    }

    const STATUS_WAIT = 0; // 待处理
    const STATUS_SUCCESS = 1; // 成功
}