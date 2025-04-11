<?php

namespace app\common\service;

use app\common\model\Channel;
use app\common\model\MemberProjectChannel;
use app\common\model\OrderTaxModel;

class OrderTaxService
{

    // 缓存key
    CONST CACHE_PROJECT_KEY = 'order_tax:';

    /**
     * 保存订单税费
     * @param $order_id
     * @param $channel_id
     * @param $member_id
     * @param $type
     *
     * @return bool
     */
    public function saveOrderTax($order_id, $channel_id, $member_id, $type = MemberProjectChannel::TYPE_IN)
    {
        if (empty($order_id) || empty($channel_id) || empty($member_id)) {
            return false;
        }

        // 获取成本税率
        $channel  = Channel::getChannelBYId($channel_id);
        if (empty($channel)) {
            return false;
        }

        // 获取会员税率
        $member_project_channel = MemberProjectChannel::getMemberProjectChannel($member_id, $channel_id, $type);
        if (empty($member_project_channel)) {
            return false;
        }

        $add = [
            'order_id' => $order_id,
            'channel_id' => $channel_id,
            'member_id' => $member_id,
            'order_type' => $type,
            'channel_rate' => $type == MemberProjectChannel::TYPE_IN ? $channel['in_rate'] : $channel['out_rate'],
            'channel_fixed_rate' => $type == MemberProjectChannel::TYPE_IN ? $channel['in_fixed_rate'] : $channel['out_fixed_rate'],
            'member_rate' => $member_project_channel['rate'],
            'member_fixed_rate' => $member_project_channel['fixed_rate'],
            'create_time' => date('Y-m-d H:i:s'),
        ];

        $res = OrderTaxModel::create($add);
        if (empty($res)) {
            return false;
        }

        // 更新缓存
        $cacheKey = self::CACHE_PROJECT_KEY . $order_id . ':' . $type;
        $cacheData = $add;

        cache($cacheKey, json_encode($cacheData), 3600);

        return true;
    }

    /**
     * 获取订单税费
     */
    public function getOrderTax($order_id, $type = MemberProjectChannel::TYPE_IN)
    {
        if (empty($order_id)) {
            return false;
        }

        $cacheKey = self::CACHE_PROJECT_KEY . $order_id . ':' . $type;
        $cacheData = cache($cacheKey);
        if ($cacheData) {
            return json_decode($cacheData, true);
        }

        $order_tax = OrderTaxModel::where(['order_id' => $order_id, 'order_type' => $type])->find();
        if (empty($order_tax)) {
            return false;
        }

        return $order_tax->toArray();
    }
}