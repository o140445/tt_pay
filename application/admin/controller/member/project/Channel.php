<?php

namespace app\admin\controller\member\project;

use app\common\controller\Backend;
use app\common\model\Member;
use app\common\model\MemberProjectChannel;
use app\common\model\MemberProjectChannel as ChannelModel;
use app\common\model\Project;
use app\common\service\OrderInService;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 会员通道费率
 *
 * @icon fa fa-circle-o
 */
class Channel extends Backend
{

    /**
     * Channel模型对象
     * @var \app\common\model\MemberProjectChannel
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ChannelModel;
        $this->view->assign("typeList", $this->model->getTypeList());

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * index
     */
    public function index($ids = null)
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
            ->with(['project', 'channel'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            $member_id = $this->request->get('member_id');
            $member = Member::find($member_id);

            $this->view->assign('member', $member);
            $projectList = Project::where('status', 1)->column('id, name');
            $subMemberList = [];
            if ($member->is_agency) {
                $subMemberList = Member::where('status', 1)->where('agency_id', $member_id)->column('id, username');
                $subMemberList[0] = '请选择代理';
                ksort($subMemberList);
            }
            // projectList 变为
            $projectList[0] = '请选择项目';
            $this->view->assign('projectList', $projectList);
            $this->view->assign('subMemberList', $subMemberList);

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
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
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
            $projectList = Project::where('status', 1)->column('id, name');
            $subMemberList = Member::where('status', 1)->where('agency_id', $row->member_id)->column('id, username');
            // projectList 变为
            $subMemberList[0] = '请选择';
            $this->view->assign('projectList', $projectList);
            $this->view->assign('subMemberList', $subMemberList);
            $row->with(['member']);


            $project = Project::with(['channel'])->find($row['project_id']);
            $channelList = [];
            if ($project->channel) {
                foreach ($project['channel'] as $channel) {
                    $channelList[$channel['id']] = $channel['title'];
                }
            }
            $this->view->assign('channelList', $channelList);

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
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
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
        $this->success();
    }

    public function in($ids = null)
    {
        if (false === $this->request->isPost()) {
            $member_id = $this->request->get('member_id');
            $data = MemberProjectChannel::where('member_id', $member_id)
                ->with(['member', 'member.area'])
                ->where('status', MemberProjectChannel::STATUS_ON)
                ->where('type', MemberProjectChannel::TYPE_IN)
                ->select();

            if (empty($data)) {
                $this->error('请先添加代收通道');
            }

            $this->view->assign('project_id', $data[0]->project_id);

            return $this->view->fetch();
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $result = false;
        Db::startTrans();
        try {
            $orderService = new OrderInService();
            $result = $orderService->memberCreateOrder($ids, $params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }


        // 新页面打开 pay_url
        $this->success('订单创建成功', '', $result);

    }

}
