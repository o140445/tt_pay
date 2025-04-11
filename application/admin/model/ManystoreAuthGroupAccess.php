<?php

namespace app\admin\model;

use think\Model;

class ManystoreAuthGroupAccess extends Model
{
    // 表名
    protected $name = 'manystore_auth_group_access';

    CONST GROUP_AGENT = 2; // 代理商
    CONST GROUP_MERCHANT = 1; // 商户

}