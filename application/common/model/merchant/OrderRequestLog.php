<?php

namespace app\common\model\merchant;

use think\Model;

class OrderRequestLog  extends Model
{
    protected $name = 'order_request_log';

    const ORDER_TYPE_IN = 1;
    const ORDER_TYPE_OUT = 2;

    const REQUEST_TYPE_REQUEST = 1; // 请求
    const REQUEST_TYPE_RESPONSE = 2; // 响应
}