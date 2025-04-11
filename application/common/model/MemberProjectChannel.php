<?php

namespace app\common\model;

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

    // 缓存key
    CONST CACHE_PROJECT_KEY = 'member_project_channel:';
    CONST CACHE_CHANNEL_KEY = 'member_project_channel:';
    
    const TYPE_IN = 1; // 代付
    const TYPE_OUT = 2; // 代收

    const TYPE_TEXT = [
        self::TYPE_IN => '代收',
        self::TYPE_OUT => '代付'
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

    /**
     * 根据项目ID和会员ID获取通道信息
     */
    public static function getChannelByProjectId($projectId, $memberId, $type = self::TYPE_IN)
    {
        if (!$projectId || !$memberId) {
            return null;
        }

        $key = self::CACHE_PROJECT_KEY . $memberId . ':' . $projectId;
        $data = cache($key);
        if (!$data) {
            $data = self::where('project_id', $projectId)
                ->where('member_id', $memberId)
                ->where('status', self::STATUS_ON)
                ->where('type', $type)
                ->order('id', 'desc')
                ->find();
            if ($data) {
                cache($key, $data);
            }
        }

        return $data;
    }

    /**
     * 根据会员ID和通道ID获取通道信息
     */
    public static function getMemberProjectChannel($memberId, $channelId, $type = self::TYPE_IN)
    {
        if (!$memberId || !$channelId) {
            return null;
        }

        $key = self::CACHE_CHANNEL_KEY . $memberId . ':' . $channelId;
        $data = cache($key);
        if (!$data) {
            $data = self::where('member_id', $memberId)
                ->where('channel_id', $channelId)
                ->where('status', self::STATUS_ON)
                ->where('type', $type)
                ->find();
            if ($data) {
                cache($key, $data);
            }
        }

        return $data;
    }

}
