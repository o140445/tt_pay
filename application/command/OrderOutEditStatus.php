<?php

namespace app\command;

use app\common\model\merchant\OrderOut;
use app\common\model\merchant\OrderRequestLog;
use app\common\service\OrderOutService;
use app\common\service\OrderRequestService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;

class OrderOutEditStatus  extends Command
{
    protected function configure()
    {
        $this->setName('order_out:edit_status')
            ->setDescription('Order Out Edit Status');
    }

    protected function execute(Input $input, Output $output)
    {
        // 获取所有未处理的订单 1个小时前的订单 10条
        $orderOut = OrderOut::where('status', 5)
            ->where('create_time', '<', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->limit(100)
            ->select();

        foreach ($orderOut as $item) {
            Db::startTrans();
            try {
                $item->status = 1;
                $item->save();

                $log = new OrderRequestService();
                $log->del($item->id,OrderRequestLog::REQUEST_TYPE_REQUEST, OrderRequestLog::ORDER_TYPE_OUT);
                $output->writeln('修改订单状态成功：' . $item->order_no);

            }catch (\Exception $e) {
                Db::rollback();
                $output->writeln('修改订单状态失败：error' . $e->getMessage() .', data:' . $item->order_no);
            }
            Db::commit();
        }
    }
}