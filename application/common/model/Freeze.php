<?php

namespace app\common\model;

use think\Model;


class Freeze extends Model
{

    // 表名
    protected $name = 'member_wallet_freeze';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
        });
    }

    // 追加属性
    protected $append = [
        'status_text'
    ];
    


    // 0:冻结 1:解冻
    public function getStatusList()
    {
        return [ 0 => __('Status Freeze'), 1 => __('Status Unfreeze')];
    }

    // 冻结类型 3:手动冻结 ，5：代付冻结， 6：提现冻结 7: 循环冻结
    public function getTypeList()
    {
        return [ 3 => __('Type 3'), 5 => __('Type 5'), 6 => __('Type 6'), 7 => __('Type 7')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
