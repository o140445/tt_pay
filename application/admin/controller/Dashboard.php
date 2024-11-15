<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }

        $today = date('Y-m-d');

        // 今日订单 总单量，完成订单，总金额，完成金额
        $todayOrder = "SELECT 
            COUNT(id) as total, 
            SUM(amount) as amount,
            SUM(IF(status = 2, 1, NULL)) as success,
            SUM(IF(status = 2, amount, 0)) as success_amount
            FROM fa_order_in WHERE create_time >= '{$today}'";

        $todayOrder = Db::query($todayOrder);

        // 今日代付 总单量，完成订单，总金额，完成金额
        $todayOutOrder = "SELECT 
            COUNT(id) as total, 
            SUM(amount) as amount,
            SUM(IF(status = 2, 1, NULL)) as success,
            SUM(IF(status = 2, amount, 0)) as success_amount
            FROM fa_order_out WHERE create_time >= '{$today}'";

        $todayOutOrder = Db::query($todayOutOrder);

        // 利润统计 今日利润，7日利润，本月利润 ，总利润
        $profit = "SELECT 
            SUM(IF(create_time >= '{$today}', profit, 0)) as today_profit,
            SUM(IF(create_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY), profit, 0)) as week_profit,
            SUM(IF(create_time >= DATE_FORMAT(NOW(),'%Y-%m-01'), profit, 0)) as month_profit,
            SUM(profit) as total_profit
            FROM fa_profit";

        $profit = Db::query($profit);

        // 余额统计，可用余额，冻结余额，
        $member_wallet = "SELECT 
            SUM(balance) as balance,
            SUM(blocked_balance) as blocked_balance
            FROM fa_member_wallet";

        $member_wallet = Db::query($member_wallet);

        // 用户统计 代理总数，商户总数
        $member = "SELECT 
            COUNT(IF(is_agency = 1, 1, NULL)) as agent,
            COUNT(IF(is_agency = 0, 1, NULL)) as merchant
            FROM fa_member";

        $member = Db::query($member);

        // 金额处理为两位小数
        if ($todayOrder) {
            $todayOrder[0]['amount'] = $todayOrder[0]['amount'] ? number_format($todayOrder[0]['amount'], 2) : 0;
            $todayOrder[0]['success_amount'] = $todayOrder[0]['amount'] ? number_format($todayOrder[0]['success_amount'], 2) : 0;
        }

        if ($todayOutOrder) {
            $todayOutOrder[0]['amount'] = $todayOutOrder[0]['amount'] ? number_format($todayOutOrder[0]['amount'], 2) : 0;
            $todayOutOrder[0]['success_amount'] = $todayOutOrder[0]['success_amount'] ? number_format($todayOutOrder[0]['success_amount'], 2) : 0;
        }

        if ($profit) {
            $profit[0]['today_profit'] = $profit[0]['today_profit'] ? number_format($profit[0]['today_profit'], 2) : 0;
            $profit[0]['week_profit'] = $profit[0]['week_profit'] ? number_format($profit[0]['week_profit'], 2) : 0;
            $profit[0]['month_profit'] = $profit[0]['month_profit'] ? number_format($profit[0]['month_profit'], 2) : 0;
            $profit[0]['total_profit'] = $profit[0]['total_profit'] ? number_format($profit[0]['total_profit'], 2) : 0;
        }

        if ($member_wallet) {
            $member_wallet[0]['balance'] = $member_wallet[0]['balance'] ? number_format($member_wallet[0]['balance'], 2) : 0;
            $member_wallet[0]['blocked_balance'] = $member_wallet[0]['blocked_balance'] ? number_format($member_wallet[0]['blocked_balance'], 2) : 0;
        }

        $this->view->assign([
            'todayOrder' => $todayOrder[0],
            'todayOutOrder' => $todayOutOrder[0],
            'profit' => $profit[0],
            'member_wallet' => $member_wallet[0],
            'member' => $member[0],
        ]);

//        $this->assignconfig('column', array_keys($userlist));
//        $this->assignconfig('userdata', array_values($userlist));

        return $this->view->fetch();
    }

}
