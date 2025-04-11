<?php

namespace app\common\model;

use think\Model;

class OrderTaxModel extends Model
{
    // 表名
    protected $name = 'order_tax';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

}