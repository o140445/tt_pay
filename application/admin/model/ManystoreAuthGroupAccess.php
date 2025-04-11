<?php

namespace app\admin\model;

use think\Model;

class ManystoreAuthGroupAccess extends Model
{
    // 表名
    protected $name = 'manystore_auth_group_access';

    CONST GROUP_AGENT = 1; // 代理商
    CONST GROUP_MERCHANT = 2; // 商户

}