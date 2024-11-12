<?php

namespace app\admin\validate\payment;

use think\Validate;

class Channel extends Validate
{
    protected $rule = [
        'title' => 'require|unique:channel',
        'code' => 'require',
        'area_id' => 'require',
    ];

    protected $message = [
        'title.require' => '通道名称不能为空',
        'title.unique' => '通道名称已存在',
        'code.require' => '通道编码不能为空',
        'area_id.require' => '地区不能为空',
    ];

    protected $scene = [
        'add'  => ['title', 'code', 'area_id'],
        'edit' => ['title', 'code', 'area_id'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'title' => __('Title'),
            'code' => __('Code'),
            'area_id' => __('Area_id'),
        ];
        parent::__construct($rules, $message, $field);
    }


}