<?php

namespace app\manystore\controller\order;

use app\common\controller\Backend;
use app\common\model\Freeze as FreezeModel;
use app\common\model\MemberWalletModel;
use app\common\service\FreezeService;
use think\Db;

/**
 * 会员冻结列管理
 *
 * @icon fa fa-circle-o
 */
class Freeze extends Backend
{

    /**
     * Freeze模型对象
     * @var \app\common\model\Freeze
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new FreezeModel;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;

        // id 和 amount 是必填的
        if (!isset($params['member_id']) || !isset($params['amount'])) {
            $this->error('参数错误');
        }

        Db::startTrans();
        try {
            $freezeService = new FreezeService();
            $remark = $params['remark'] ? $params['remark'] : '手动冻结';
            $result = $freezeService->freeze($params['member_id'], $params['amount'], MemberWalletModel::CHANGE_TYPE_FREEZE, '', $remark);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


    public function multi($ids = null)
    {
        $this->success('ok');
    }
    public function edit($ids = null)
    {
        $this->success('ok');
    }
    public function del($ids = null)
    {
         $this->success('ok');
    }

    //unfreeze
    public function unfreeze($ids = null)
    {

        $result = false;

        // id 是必填的
        if (!isset($ids) || empty($ids)) {
            $this->error('参数错误');
        }

        Db::startTrans();
        try {
            $freezeService = new FreezeService();
            $result = $freezeService->unfreeze(MemberWalletModel::CHANGE_TYPE_UNFREEZE, $ids, '');
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success('解冻成功');
    }

}
