<?php

namespace app\common\model\merchant;

use think\Model;


class OrderManual extends Model
{

    // 表名
    protected $name = 'order_manual';


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
    

    







}
