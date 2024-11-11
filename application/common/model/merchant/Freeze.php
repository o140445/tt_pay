<?php

namespace app\common\model\merchant;

use think\Model;


class Freeze extends Model
{

    // 表名
    protected $name = 'member_wallet_freeze';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

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
