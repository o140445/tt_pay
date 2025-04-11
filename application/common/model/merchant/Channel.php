<?php

namespace app\common\model\merchant;

use think\Model;

class Channel extends Model
{
    // 表名
    protected $name = 'channel';


    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 删除状态
    CONST STATUS_NORMAL = 1; // 正常
    CONST STATUS_DISABLE = 0; // 禁用

    CONST STATUS_DELETE = 2; // 删除

    // 缓存key
    CONST CACHE_KEY = 'channel:';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
            $row->update_time = date('Y-m-d H:i:s');
        });

        self::beforeUpdate(function ($row) {
            $row->update_time = date('Y-m-d H:i:s');
        });
    }




    public function getStatusList()
    {
        return ['0' => __('Hidden'), '1' => __('Normal')];
    }
}