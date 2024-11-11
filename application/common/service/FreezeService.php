<?php

namespace app\common\service;

use app\common\model\merchant\MemberWalletFreezeModel;

class FreezeService
{
    // 冻结单
    public function freeze($member_id, $amount, $type, $order_no = '', $remark = '手动冻结')
    {
        $walletService = new MemberWalletService();
        $amount = abs($amount);
        $wallet = $walletService->getWalletInfo($member_id);
        if (!$wallet) {
            throw new \Exception('用户不存在');
        }


        if  ($wallet->balance < $amount) {
            throw new \Exception('余额不足');
        }
        // 添加冻结记录
        $order_no = $order_no ?: $this->freezeOrderNo('SDDJ'.$member_id);
        $res = $this->addFreeze($member_id, $amount, $type, $order_no, $remark);
        if (!$res) {
            throw new \Exception('冻结失败');
        }

        // 减少余额
        $walletService->freeze($member_id, $amount, $type, $order_no, $remark);

        return true;
    }

    // 解冻
    public function unfreeze($type, $id = ' ', $order_no = '', $remark = '手动解冻')
    {
        if (!$id && !$order_no) {
            throw new \Exception('参数错误');
        }

        if ($order_no) {
            $freeze = MemberWalletFreezeModel::where('order_no', $order_no)->where('status', MemberWalletFreezeModel::STATUS_WAIT)->lock(true)->find();
        } else {
            $freeze = MemberWalletFreezeModel::where('id', $id)->where('status', MemberWalletFreezeModel::STATUS_WAIT)->lock(true)->find();
        }

        if (!$freeze) {
            throw new \Exception('冻结单不存在');
        }
        $freeze->status = MemberWalletFreezeModel::STATUS_SUCCESS;
        $freeze->thaw_time = date('Y-m-d H:i:s');
        $res = $freeze->save();
        if (!$res) {
            throw new \Exception('解冻失败');
        }

        $walletService = new MemberWalletService();
        $walletService->unfreeze($freeze->member_id, $freeze->amount, $type, $freeze->order_no, $remark);

        return true;
    }



    public function freezeOrderNo($str)
    {
        $order_no = get_order_no($str);
        return $order_no;
    }


    /**
     * 添加冻结记录
     * @param $member_id
     * @param $amount
     * @param $type
     * @param $remark
     * @return MemberWalletFreezeModel
     */
    public function addFreeze($member_id, $amount, $type, $order_no = '', $remark = '')
    {
        if ($amount <= 0) {
            throw new \Exception('冻结金额必须大于0');
        }

        $data = [
            'member_id' => $member_id,
            'amount' => $amount,
            'freeze_type' => $type,
            'remark' => $remark,
            'order_no' => $order_no,
        ];

        return MemberWalletFreezeModel::create($data);
    }
}