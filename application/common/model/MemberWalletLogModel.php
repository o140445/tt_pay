<?php

namespace app\common\model;

use think\Model;

class MemberWalletLogModel extends Model
{
    protected $name = 'member_wallet_log';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
        });
    }
}