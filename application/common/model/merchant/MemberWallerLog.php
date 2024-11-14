<?php

namespace app\common\model\merchant;

use think\Model;


class MemberWallerLog extends Model
{
    // 表名
    protected $name = 'member_wallet_log';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
        });

        self::beforeUpdate(function ($row) {
            $row->update_time = date('Y-m-d H:i:s');
        });
    }
    // 追加属性
    protected $append = [

    ];
    

    







}
