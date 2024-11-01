<?php

namespace app\admin\model\order;

use think\Model;


class In extends Model
{

    

    

    // 表名
    protected $name = 'order_in';
    
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

    // 状态 0未支付 1已支付 2失败 3退款
    const STATUS_UNPAID = 0;
    const STATUS_PAID = 1;
    const STATUS_FAILED = 2;
//    const STATUS_REFUND = 3;

    
    public function getStatusList()
    {
        return [
            self::STATUS_UNPAID => __('Status unpaid'),
            self::STATUS_PAID => __('Status paid'),
            self::STATUS_FAILED => __('Status failed'),
//            self::STATUS_REFUND => __('Status refund')
        ];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


}
