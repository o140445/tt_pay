<?php

namespace app\common\model\merchant;

use think\Model;


class MemberProjectChannel extends Model
{

    // 表名
    protected $name = 'member_project_channel';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    
    const TYPE_IN = 1; // 代付
    const TYPE_OUT = 2; // 代收

    const TYPE_TEXT = [
        self::TYPE_IN => '代付',
        self::TYPE_OUT => '代收'
    ];

    // status 1启用 0禁用
    const STATUS_ON = 1;
    const STATUS_OFF = 0;

    public function getTypeList()
    {
        return self::TYPE_TEXT;
    }


    // project
    public function project()
    {
        return $this->hasOne('Project', 'id', 'project_id');
    }

    // channel
    public function channel()
    {
        return $this->hasOne('Channel', 'id', 'channel_id');
    }

    // member
    public function member()
    {
        return $this->hasOne('Member', 'id', 'member_id');
    }


}
