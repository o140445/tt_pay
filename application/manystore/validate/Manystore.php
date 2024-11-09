<?php

namespace app\manystore\validate;

use think\Validate;

class Manystore extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|regex:\w{3,12}|unique:manystore',
        'nickname' => 'require|length:0,10|regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_-]+$/u',
        'password' => 'require|regex:\S{32}',
        'email'    => 'require|email|unique:manystore,email',
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['username', 'email', 'nickname', 'password'],
        'edit' => ['username', 'email', 'nickname', 'password'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => __('Username'),
            'nickname' => __('Nickname'),
            'password' => __('Password'),
            'email'    => __('Email'),
        ];
        $this->message = array_merge($this->message, [
            'username.regex' => __('Please input correct username'),
            'password.regex' => __('Please input correct password'),
            'nickname.regex' => __('Please input correct nickname'),
            'nickname.length' => __('Please input length nickname'),
        ]);
        parent::__construct($rules, $message, $field);
    }

}
