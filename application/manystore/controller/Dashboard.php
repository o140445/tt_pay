<?php

namespace app\manystore\controller;

use app\common\controller\ManystoreBase;
use app\common\model\merchant\Member;
use app\common\model\merchant\MemberWalletModel;
use app\common\model\merchant\OrderIn;
use app\common\model\merchant\OrderOut;
use app\common\model\merchant\Profit;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends ManystoreBase
{

    /**
     * 查看
     */
    public function index()
    {
        $start_date = date('Y-m-d', strtotime('-10 days'));
        $end_date = date('Y-m-d');
        $date_list = [];

        $member = Member::with(['wallet'])->where('id',STORE_ID)->find();

        $today_in_amount = $today_out_amount =  $total_user = $today_commission = 0;
        $in_amount = $out_amount = $commission = 0;

        $order_list = [];
        if (!$member->is_agency) {
            $today_in_amount = OrderIn::where('member_id', STORE_ID)
                ->where('status', OrderIn::STATUS_PAID)
                ->where('create_time', '>=', date('Y-m-d 00:00:00'))
                ->sum('actual_amount');

            $today_out_amount = OrderOut::where('member_id', STORE_ID)
                ->where('status', OrderOut::STATUS_PAID)
                ->where('create_time', '>=', date('Y-m-d 00:00:00'))
                ->sum('actual_amount');


            // 获取每日收益
            $in_amount = OrderIn::where('member_id', STORE_ID)
                ->where('status', OrderIn::STATUS_PAID)
                ->where('create_time', '>=', $start_date . ' 00:00:00')
                ->where('create_time', '<=', $end_date . ' 23:59:59')
                ->group('date(create_time)')
                ->column('sum(actual_amount) as amount', 'date(create_time)');

            $out_amount = OrderOut::where('member_id', STORE_ID)
                ->where('status', OrderOut::STATUS_PAID)
                ->where('create_time', '>=', $start_date . ' 00:00:00')
                ->where('create_time', '<=', $end_date . ' 23:59:59')
                ->group('date(create_time)')
                ->column('sum(actual_amount) as amount', 'date(create_time)');

        }else{
            $users = Member::where('agent_id', STORE_ID)->column('id');
            $total_user = count($users);
            $today_commission = Profit::where('member_id', 'in', $users)
                ->where('create_time', '>=', date('Y-m-d 00:00:00'))
                ->sum('commission');
            $commission = Profit::where('member_id', 'in', $users)
                ->where('create_time', '>=', $start_date . ' 00:00:00')
                ->where('create_time', '<=', $end_date . ' 23:59:59')
                ->group('date(create_time)')
                ->column('sum(commission) as amount', 'date(create_time)');

        }

        // 循环获取日期
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        while ($start <= $end) {
            $date = date('Y-m-d', $start);

            if (!$member->is_agency) {
                if (!isset($in_amount[$date])) {
                    $in_amount[$date] = 0;
                }

                if (!isset($out_amount[$date])) {
                    $out_amount[$date] = 0;
                }
            }else{
                if (!isset($commission[$date])) {
                    $commission[$date] = 0;
                }
            }

            $date_list[] = $date;
            $start += 86400;
        }

        if ($in_amount) {
            ksort($in_amount);
            $data = array_values($in_amount);
            $order_list['in']['list'] = $data;
            $order_list['in']['name'] = '代收金额';
        }

        if ($out_amount) {
            ksort($out_amount);
            $data = array_values($out_amount);
            $order_list['out']['list'] = $data;
            $order_list['out']['name'] = '代付金额';
        }

        if ($commission) {
            ksort($commission);
            $data = array_values($commission);
            $order_list['commission']['list'] = $data;
            $order_list['commission']['name'] = '佣金';
        }

        $order_list = array_values($order_list);


        $this->view->assign([
            'balance'          => $member->wallet->balance,
            'blocked_balance'  => $member->wallet->blocked_balance,
            'today_in_amount'  => $today_in_amount,
            'today_out_amount' => $today_out_amount,
            'total_user'       => $total_user,
            'today_commission' => $today_commission,
            'is_agency'        => $member->is_agency,
            'date_list'             => $date_list,
            'order_list'            => $order_list,
        ]);

        return $this->view->fetch();
    }

}
