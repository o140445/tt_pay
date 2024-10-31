<?php

namespace app\admin\model;

use think\Model;

class MemberWalletFreezeModel extends  Model
{
    protected $name = 'member_wallet_freeze';

    const STATUS_WAIT = 0; // 待处理
    const STATUS_SUCCESS = 1; // 成功
}