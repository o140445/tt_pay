<?php

namespace app\admin\validate;

use think\Validate;

class Member extends Validate
{
    protected $rule = [
        'username' => 'require|unique:member',
        'email' => 'require|email|unique:member',
        'password' => 'length:6,32',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username', 'email', 'password'],
        'edit' => [],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => __('UserName'),
            'email'    => __('Email'),
            'password' => __('Password'),
        ];
        parent::__construct($rules, $message, $field);
    }

}