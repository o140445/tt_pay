<?php

namespace app\common\model\merchant;

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