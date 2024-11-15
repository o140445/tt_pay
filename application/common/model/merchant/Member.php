<?php

namespace app\common\model\merchant;

use think\Model;

class Member extends Model
{
    protected $name = 'member';
    protected $pk = 'id';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
            $row->update_time = date('Y-m-d H:i:s');
            $row->last_login_time = date('Y-m-d H:i:s');
        });
        self::beforeUpdate(function ($row) {
            $row->update_time = date('Y-m-d H:i:s');
        });
    }

    // 分类
    public $category = [
        0 => '商户',
        1 => '代理商',
    ];

    // 对接类型 docking_type
    CONST DOCKING_TYPE_API = 1;
    CONST DOCKING_TYPE_WEB = 0;

    // status
    CONST STATUS_NORMAL = 1;
    CONST STATUS_DISABLE = 0;

    public function getAgentLists()
    {
        $data = self::where('is_agency', 1)->column('id,username');
        $data[0] = '无';
        return $data;
    }

    // wallet
    public function wallet()
    {
        return $this->hasOne('MemberWalletModel', 'member_id', 'id');
    }

    // area
    public function area()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }
}
