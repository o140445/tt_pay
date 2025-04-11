<?php

namespace app\admin\controller\member;

use app\admin\model\AuthGroupAccess;
use app\admin\model\ManystoreAuthGroupAccess;
use app\common\controller\Backend;
use app\common\model\merchant\ConfigArea;
use app\common\model\merchant\Member as MemberModel;
use app\common\model\merchant\MemberWalletModel;
use app\common\service\FreezeService;
use app\common\service\MemberWalletService;
use PragmaRX\Google2FAQRCode\Google2FA;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class Member extends Backend
{
    protected $model = null;


    protected $modelValidate = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MemberModel();
        $this->view->assign("categoryList", $this->model->category);
        $this->view->assign('agentLists', $this->model->getAgentLists());
    }


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
            ->with(['wallet'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);

        // 删除不需要的字段
        $data = $list->items();
        foreach ($data as $key => $value) {
            unset($data[$key]['password']);
            unset($data[$key]['salt']);
        }


        $result = ['total' => $list->total(), 'rows' => $data];
        return json($result);
    }

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

        // 机构检查
        if (isset($params['agency_id']) && $params['agency_id'] && $params['agency_id'] != 0) {
            $agency = $this->checkAgent($params['agency_id']);
            if (!$agency) {
                $this->error('代理不存在');
            }
        }

        if (!$params['password']) {
            $this->error('密码不能为空');
        }


        $params['api_key'] = strtoupper(md5($params['username'] . time()));


        // 密码长度检查
        if (strlen($params['password']) < 6 || strlen($params['password']) > 20) {
            $this->error('密码长度必须在6-20之间');
        }

        // username 是否重复
        $member = MemberModel::where('username', $params['username'])->find();
        if ($member) {
            $this->error('用户名已存在');
        }

        // 获取商户号
        $params['mch_id'] = $this->getMchId();

        // google验证器
        $params['google_token'] = $this->model->generateGoogleToken();

        $result = false;
        Db::startTrans();
        try {

            // 密码加密
            if ($params['password']) {
                $params['salt'] = get_random_string(6);
                $params['password'] = md5($params['password'].$params['salt']);
            }

            $result = $this->model->allowField(true)->save($params);

            // 创建钱包
            MemberWalletModel::create([
                'member_id' => $this->model->id,
                'balance' => 0,
                'blocked_balance' => 0
            ]);

            // 关联角色
            ManystoreAuthGroupAccess::create(
                [
                    'uid' => $this->model->id,
                    'group_id' => $params['is_agency'] ? ManystoreAuthGroupAccess::GROUP_AGENT : ManystoreAuthGroupAccess::GROUP_MERCHANT // 2 代理商 1 商户
                ]
            );

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }

        // 写入缓存
        $this->setCache($this->model->id);

        $this->success();
    }


    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            // 机构检查
            if (isset($params['agency_id']) && $params['agency_id']) {
                $agency = $this->checkAgent($params['agency_id']);
                if (!$agency) {
                    $this->error('机构不存在');
                }
            }

            if (isset($params['is_agency'])) {
                unset($params['is_agency']);
            }

            // 密码更新
            if (isset($params['password']) && !empty($params['password'])) {

                // 密码长度检查
                if (strlen($params['password']) < 6 || strlen($params['password']) > 20) {
                    $this->error('密码长度必须在6-20之间');
                }

                $params['salt'] = get_random_string(6);
                $params['password'] = md5($params['password'].$params['salt']);
            } else {
                $params['password'] = $row->password;
                $params['salt'] = $row->salt;
            }

            //username 和 email 是否传递
            if (!isset($params['username']) || empty($params['username'])){
                $this->error('用户名不能为空');
            }

            // username 和 email 是否重复
            $member = MemberModel::where('username', $params['username'])->find();
            if ($member && $member->id != $row->id) {
                $this->error('用户名或邮箱已存在');
            }

            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }

        // 写入缓存
        $this->setCache($row->id);

        $this->success();
    }

    // checkAgent
    protected function checkAgent($agency_id)
    {
        $agency = MemberModel::where('id', $agency_id)->where('is_agency', 1)->where('status', 1)->find();
        if ($agency) {
            return true;
        }
        return false;
    }
    
    // edit_password
    public function resetApiKey($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid Request'));
        }
        $result = false;
        Db::startTrans();
        try {
            $row->api_key = strtoupper(md5($row->username . time()));
            $result = $row->allowField(true)->save([
                'api_key' => $row->api_key
            ]);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }

        // 删除缓存
        $this->setCache($row->id);

        $this->success("ok");
    }

    // addBalance
    public function addBalance($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $row->with('wallet');
            $this->view->assign('row', $row);
            $this->view->assign('changeType', MemberWalletModel::CHANGE_MANUAL_TYPE);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $result = false;
        Db::startTrans();
        try {
            $walletService = new MemberWalletService();
            switch ($params['type']) {
                case MemberWalletModel::CHANGE_TYPE_ADD:

                    $remark = $params['remark'] ? $params['remark'] : '手动添加';
                    $result = $walletService->addBalance($row->id, $params['amount'], $remark);
                    break;
                case MemberWalletModel::CHANGE_TYPE_SUB:

                    $remark = $params['remark'] ? $params['remark'] : '手动减少';
                    $result = $walletService->subBalance($row->id, $params['amount'], $remark);
                    break;
                case MemberWalletModel::CHANGE_TYPE_FREEZE:
                    $freezeService = new FreezeService();
                    $remark = $params['remark'] ? $params['remark'] : '手动冻结';
                    $result = $freezeService->freeze($row->id, $params['amount'], MemberWalletModel::CHANGE_TYPE_FREEZE, '', $remark);
                    break;
                default:
                    $this->error('未知操作');
                    break;
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success("ok");
    }

    /**
     * resetGoogle
     * 重置谷歌验证码
     */
    public function resetGoogle($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid Request'));
        }
        $result = false;
        Db::startTrans();
        try {
            $update['google_token'] = $this->model->generateGoogleToken();
            $update['is_bind_google'] = 0;
            $update['is_verify_google'] = 0;
            $update['new_google_token'] = 0;
            $result = $row->allowField(true)->save($update);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }

        $this->success("ok");
    }


    // getMchId
    protected function getMchId()
    {
        $mch_id = get_merchant_no();
        $member = MemberModel::where('mch_id', $mch_id)->find();
        if ($member) {
            $mch_id = $this->getMchId();
        }
        return $mch_id;
    }

    /**
     * 写入缓存
     */
    protected function setCache($id)
    {
        $key = MemberModel::CACHE_KEY . $id;
        // 删除
        if (cache($key)) {
            cache($key, null);
        }

        $member = MemberModel::where('id', $id)->where('status', MemberModel::STATUS_NORMAL)->find();
        if ($member) {
            $member =  json_encode($member->toArray());
            cache($key, $member);
            return $member;
        }
        return false;
    }

}