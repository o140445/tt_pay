<?php

namespace app\admin\model;

use think\Model;

class ProfitStat extends Model
{
    // 表名
    protected $name = 'profit_stat';

    // area
    public function area()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }

}