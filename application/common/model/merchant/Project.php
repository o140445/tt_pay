<?php

namespace app\common\model\merchant;

use think\Model;

class Project extends Model
{
    // 表名
    protected $name = 'project';


    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $row->create_time = date('Y-m-d H:i:s');
            $row->update_time = date('Y-m-d H:i:s');
        });

        self::beforeUpdate(function ($row) {
            $row->update_time = date('Y-m-d H:i:s');
        });
    }


    //projectChannel
    public function projectChannel()
    {
        return $this->hasMany('ProjectChannel', 'project_id', 'id');
    }

    // channel 多对多
    public function channel()
    {
        return $this->belongsToMany('Channel', 'project_channel', 'channel_id', 'project_id');
    }

    // configArea
    public function configArea()
    {
        return $this->hasOne('ConfigArea', 'id', 'area_id');
    }

}