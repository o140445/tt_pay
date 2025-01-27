<?php

namespace app\command;

use app\common\model\merchant\OrderOut;
use app\common\service\OrderOutService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;

/**
 * 代付失败通知
 */
class OrderOutFailed extends Command
{
    protected function configure()
    {
        $this->setName('order_out:failed')
            ->setDescription('Order Out Failed');
    }

    protected function execute(Input $input, Output $output)
    {
        // 获取所有未处理的订单 1分钟前的订单 100条
        $orderOut = OrderOut::where('member_id', 90007)
            ->where('status', 5)
            ->where('create_time', '<', date('Y-m-d H:i:s', strtotime('-1 minute')))
            ->limit(100)
            ->select();

        $outService = new OrderOutService();
        foreach ($orderOut as $item) {

            Db::startTrans();
            try {
                 $outService->failOrder($item, []);
            }catch (\Exception $e) {
                Db::rollback();
                $output->writeln('代付回调请求失败：error' . $e->getMessage() .', data:' . $item->member_order_no);
            }
            Db::commit();

                Db::startTrans();
                try {
                    $outService->notifyDownstream($item->id);
                }catch (\Exception $e) {
                    Db::rollback();
                    $output->writeln('代付回调通知下游失败：error' . $e->getMessage() .', order_id:' . $item->member_order_no);
                }
                Db::commit();

        }
    }
}