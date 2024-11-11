<?php

namespace app\common\service;

use app\common\model\merchant\OrderRequestLog;

class OrderRequestService
{
    /**
     * 创建
     */
    public function create($order_no, $request_type, $order_type,  $request_data, $response_data)
    {
        $model = new OrderRequestLog();
        $model->order_no = $order_no;
        $model->request_type = $request_type;
        $model->order_type = $order_type;
        $model->request_data = $request_data;
        $model->response_data = $response_data;
        $model->save();
    }
}