<?php

namespace app\common\model;

use think\Model;

class ProfitStatModel extends Model
{
    // 表名
    protected $name = 'profit_stat';

    // area
    public function area()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }

}