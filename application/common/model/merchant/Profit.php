<?php

namespace app\common\model\merchant;

use think\Model;


class Profit extends Model
{

    

    

    // 表名
    protected $name = 'profit';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    // area
    public function area()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }

    







}
