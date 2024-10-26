<?php

namespace app\admin\model;

use think\Model;

class Channel extends Model
{
    // 表名
    protected $name = 'channel';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->createtime = time();
        });
    }
}