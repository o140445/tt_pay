<?php

namespace app\admin\model;

use think\Model;


class WithdrawOrder extends Model
{

    

    

    // 表名
    protected $name = 'withdraw_order';
    
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

    // 状态 0:待审核 1:已通过 2:已拒绝
    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;
    
    public function getStatusList()
    {
        return ['0' => __('待审核'), '1' => __('已通过'), '2' => __('已拒绝')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
