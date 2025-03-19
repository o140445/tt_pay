<?php

namespace app\command;

use app\common\model\merchant\Channel;
use app\common\model\merchant\OrderOut;
use app\common\service\OrderOutService;
use app\common\service\PaymentService;
use think\console\Command;
use think\Db;
use think\Log;

class QueryOutOrderEditStatus extends Command
{
    protected function configure()
    {
        $this->setName('query_out_order_edit_status:notify')
            ->setDescription('Query Out Order Edit Status');
    }

    protected function execute($input, $output)
    {
        $output->writeln('Query Out Order Edit Status start');
        Log::init([
            'type'  => 'File',
            'path'  => LOG_PATH . 'pay/out/',
            'level' => ['error', 'info'],
        ]);
        // 获取支付类型等于 NovoPay 的通道id
        $payChannel = 'NovoPay';
        $channel_ids = Channel::where('code', $payChannel)->column('id');

        if (empty($channel_ids)) {
            Log::write('通道不存在', 'error');
            return;
        }

        // 获取所有处理中的订单 20条
        $orderOut = OrderOut::with('channel')
            ->where('status', OrderOut::STATUS_PAYING)
            ->whereIn('channel_id', $channel_ids)
            ->limit(20)
            ->select();
        // 打印统计
        $output->writeln('Query Out Order Edit Status count:'. count($orderOut));

        $outService = new OrderOutService();
        $orderPaymentService = new PaymentService($payChannel);
        foreach ($orderOut as $item) {
            $res =  $orderPaymentService->queryOrder($item->channel, $item->channel_order_no);
            $output->writeln('代付查询结果:'. json_encode($res));
            Log::write('代付查询结果：order_no ' . $item->order_no .', data:' . json_encode($res), 'info');
            if ($res['status'] == 0) {
                Log::write('代付查询失败：order_no ' . $item->order_no .', data:' . json_encode($res), 'error');
                $output->writeln('代付查询失败:'. $item->order_no);
                continue;
            }

            // 修改订单状态
            Db::startTrans();
            try {
                $res =   $outService->notify($item->channel->sign, $res);
                Db::commit();
            }catch (\Exception $e) {
                Db::rollback();
                Log::write('代付回调失败：error' . $e->getMessage() .', data:' . json_encode($res), 'error');
            }

            // 成功
            if ($res['order_id']) {
                Db::startTrans();
                try {
                    $outService->notifyDownstream($res['order_id']);
                }catch (\Exception $e) {
                    Db::rollback();
                    Log::write('代付回调通知下游失败：error' . $e->getMessage() .', order_id:' . $res['order_id'], 'error');
                }
                Db::commit();
            }else{
                Db::rollback();
            }

            Log::write('代付请求成功：order_no' . $item->order_no .', data:' . json_encode($res), 'info');
            $output->writeln('修改订单状态成功:'. $item->order_no);
        }
    }
}