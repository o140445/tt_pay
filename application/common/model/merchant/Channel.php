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

    //config_area
    public function configArea()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }
}