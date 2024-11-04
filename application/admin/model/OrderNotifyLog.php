<?php

namespace app\admin\model;

use think\Model;

class OrderNotifyLog extends Model
{
    protected $name = 'notify_log';

    // STATUS_NOTIFY_SUCCESS
    const STATUS_NOTIFY_SUCCESS = 1;
    // STATUS_NOTIFY_FAIL
    const STATUS_NOTIFY_FAIL = 2;

    public function getNotifyStatusAttr($value = '')
    {
        $status = [
            self::STATUS_NOTIFY_SUCCESS => '通知成功',
            self::STATUS_NOTIFY_FAIL => '通知失败',
        ];

        if ($value === '') {
            return $status;
        }

        return $status[$value];
    }
}