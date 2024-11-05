<?php

namespace app\admin\model;

use think\Model;


class OrderOut extends Model
{

    

    

    // 表名
    protected $name = 'order_out';
    
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


    // 状态 1未支付 2已支付 3失败 4退款
    const STATUS_UNPAID = 1;
    const STATUS_PAID = 2;
    const STATUS_FAILED = 3;
    const STATUS_REFUND = 4;

    
    public function getStatusList()
    {
        return [
            self::STATUS_UNPAID => __('Status unpaid'),
            self::STATUS_PAID => __('Status paid'),
            self::STATUS_FAILED => __('Status failed'),
            self::STATUS_REFUND => __('Status refund')
        ];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }



    // area
    public function area()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }


}
