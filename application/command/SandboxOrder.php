<?php

namespace app\command;

use app\common\model\OrderSandbox;
use app\common\service\OrderSandboxService;
use think\console\Command;

class SandboxOrder extends Command
{
    protected function configure()
    {
        $this->setName('sandbox:order')
            ->setDescription('沙箱订单');
    }

    protected function execute($input, $output)
    {
        // 查询通知少余三次的订单 2天内的
        $data = OrderSandbox::where('notify_count', '<', 3)->order('id', 'desc')->select();

        // 循环通知
        $orderService = new OrderSandboxService();
        foreach ($data as $item) {
            // 如果是未完成的订单，修改状态

            if ($item->status == 1) {
                // 金额小于10的订单，直接成功，否则失败
                if ($item->amount <= 10) {
                    $item->status = 2;
                    $item->save();
                } else {
                    $item->status = 3;
                    $item->save();
                }
            }

            try {
                $orderService->notify($item->order_no);
            }catch (\Exception $e) {
                $output->writeln('订单号：' . $item->order_no . ' 通知失败');
                continue;
            }
            $output->writeln('订单号：' . $item->order_no . ' 通知成功');
        }

        $output->writeln('通知完成');
    }
}