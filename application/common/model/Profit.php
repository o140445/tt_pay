<?php

namespace app\common\model;

use think\Model;


class Profit extends Model
{
    // 表名
    protected $name = 'profit';

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
    // 追加属性
    protected $append = [

    ];


    // area
    public function area()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }

    







}
