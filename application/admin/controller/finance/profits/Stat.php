<?php

namespace app\admin\controller\finance\profits;

use app\admin\model\ConfigArea;
use app\admin\model\MemberStatModel;
use app\admin\model\OrderIn;
use app\admin\model\ProfitStatModel;
use app\common\controller\Backend;

/**
 * 利润报表统计
 *
 * @icon fa fa-circle-o
 */
class Stat extends Backend
{

    /**
     * Stat模型对象
     * @var \app\admin\model\ProfitStatModel
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ProfitStatModel();

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->with(['area'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
    public function add()
    {
        $this->error('请求错误');
    }

    public function edit($ids = null)
    {
        $this->error('请求错误');
    }

    public function del($ids = "")
    {
        $this->error('请求错误');
    }

    public function multi($ids = "")
    {
        $this->error('请求错误');
    }

    public function destroy($ids = "")
    {
        $this->error('请求错误');
    }


    public function restore($ids = "")
    {
        $this->error('请求错误');
    }

    public function recyclebin()
    {
        $this->error('请求错误');
    }

    /**
     * 获取统计数据
     * @return \think\response\Json
     */
    public function count()
    {

        // 示例数据，多条折线
//        $data = [
//            'xAxis' => ['2024-11-01', '2024-11-02', '2024-11-03', '2024-11-04'],
//            'series' => [
//                [
//                    'name' => 'Series 1',
//                    'data' => [120, 200, 150, 80]
//                ],
//                [
//                    'name' => 'Series 2',
//                    'data' => [90, 160, 130, 100]
//                ],
//                [
//                    'name' => 'Series 3',
//                    'data' => [60, 140, 110, 90]
//                ]
//            ]
//        ];

        //开始时间 7天前
        $start = date('Y-m-d', strtotime('-9 days'));
        //结束时间
        $end = date('Y-m-d');

        $profit_res = "SELECT
            area_id,
            SUM(profit) AS profit,
            DATE_FORMAT(create_time, '%Y-%m-%d') AS date
            FROM fa_profit WHERE create_time BETWEEN '{$start}' AND '{$end}' GROUP BY area_id, date";

        $profit_res = db()->query($profit_res);

        $area = ConfigArea::column('id,name');
//
//        $in_order_sql = "SELECT
//            SUM(IF(status = 2, true_amount, 0)) AS profit,
//            DATE_FORMAT(create_time, '%Y-%m-%d') AS date
//            FROM fa_order_in WHERE create_time BETWEEN '{$start}' AND '{$end}' GROUP BY date";
//        $in_order_res = db()->query($in_order_sql);
//
//        $out_order_sql = "SELECT
//            SUM(IF(status = 2, amount, 0)) AS profit,
//            DATE_FORMAT(create_time, '%Y-%m-%d') AS date
//            FROM fa_order_out WHERE create_time BETWEEN '{$start}' AND '{$end}' GROUP BY date";
//        $out_order_res = db()->query($out_order_sql);


        $data = [];
        $list = [];
        foreach ($profit_res as  $key => $item) {
            $list[$item['area_id']][$item['date']] = $item['profit'];
            $list[$item['area_id']]['name'] = $area[$item['area_id']];
        }

//        foreach ($in_order_res as $key => $item) {
//            $list['in'][$item['date']] = $item['profit'];
//            $list['in']['name'] = '代收';
//        }
//
//        foreach ($out_order_res as $key => $item) {
//            $list['out'][$item['date']] = $item['profit'];
//            $list['out']['name'] = '代付';
//        }


        // 循环
        while (strtotime($start) <= strtotime($end)) {
            $data['xAxis'][] = $start;
            foreach ($list as $key => $item) {
                if (!isset($data['series'][$key])) {
                    $data['series'][$key] = [
                        'name' => $item['name'],
                        'data' => [0]
                    ];
                }else{
                    $data['series'][$key]['data'][] = $item[$start] ?? 0;
                }
            }

            $start = date('Y-m-d', strtotime($start . ' +1 day'));
        }

        // 处理area
        $data['series'] = array_values($data['series']);
        return json(['code' => 1, 'data' => $data, 'msg' => 'success']);
    }

    /**
     * 获取统计数据
     * @return \think\response\Json
     */
    public function order(){

        //开始时间 7天前
        $start = date('Y-m-d', strtotime('-9 days'));
        //结束时间
        $end = date('Y-m-d');

        $in_order_sql = "SELECT
            SUM(IF(status = 2, true_amount, 0)) AS profit,
            DATE_FORMAT(create_time, '%Y-%m-%d') AS date
            FROM fa_order_in WHERE create_time BETWEEN '{$start}' AND '{$end}' GROUP BY date";
        $in_order_res = db()->query($in_order_sql);

        $out_order_sql = "SELECT
            SUM(IF(status = 2, amount, 0)) AS profit,
            DATE_FORMAT(create_time, '%Y-%m-%d') AS date
            FROM fa_order_out WHERE create_time BETWEEN '{$start}' AND '{$end}' GROUP BY date";
        $out_order_res = db()->query($out_order_sql);

        $data = [];
        $list = [];

        foreach ($in_order_res as $key => $item) {
            $list['in'][$item['date']] = $item['profit'];
            $list['in']['name'] = '代收金额';
        }

        foreach ($out_order_res as $key => $item) {
            $list['out'][$item['date']] = $item['profit'];
            $list['out']['name'] = '代付金额';
        }


        // 循环
        while (strtotime($start) <= strtotime($end)) {
            $data['xAxis'][] = $start;
            foreach ($list as $key => $item) {
                if (!isset($data['series'][$key])) {
                    $data['series'][$key] = [
                        'name' => $item['name'],
                        'data' => [0]
                    ];
                }else{
                    $data['series'][$key]['data'][] = $item[$start] ?? 0;
                }
            }

            $start = date('Y-m-d', strtotime($start . ' +1 day'));
        }

        // 处理area
        $data['series'] = array_values($data['series']);
        return json(['code' => 1, 'data' => $data, 'msg' => 'success']);
    }
}
