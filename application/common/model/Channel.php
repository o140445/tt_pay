<?php

namespace app\common\model;

use think\Model;

class Channel extends Model
{
    // 表名
    protected $name = 'channel';


    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 删除状态
    const STATUS_NORMAL = 1; // 正常
    const STATUS_DISABLE = 0; // 禁用

    const STATUS_DELETE = 2; // 删除

    // 缓存key
    const CACHE_KEY = 'channel:';

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


    public function getStatusList()
    {
        return ['0' => __('Hidden'), '1' => __('Normal')];
    }


    /**
     * 根据id获取渠道信息
     * @param $id
     * @return array|bool|mixed|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChannelBYId($id)
    {
        $cacheKey = self::CACHE_KEY . $id;
        $channel = cache($cacheKey);
        if (!$channel) {
            $channel = self::where('id', $id)
                ->where('status', self::STATUS_NORMAL)
                ->find();
            if ($channel) {
                cache($cacheKey, $channel);
            }
        }
        return $channel;
    }
}