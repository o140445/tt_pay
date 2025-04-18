<?php

namespace app\admin\controller\payment;

use app\common\controller\Backend;
use app\common\model\Channel;
use app\common\model\Project as ProjectModel;
use app\common\model\ProjectChannel;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class Project extends Backend
{
    /**
     * @var ProjectModel
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        $this->model = new ProjectModel();
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
            ->with(['projectChannel', 'projectChannel.channel'])
            ->where($where)
            ->where('status', '<>', ProjectModel::STATUS_DELETE)
            ->order($sort, $order)
            ->paginate($limit);


        $data = $list->items();
        foreach ($data as $k => $v) {
            $project_channel = $v->projectChannel;
            if (!isset($project_channel) || empty($project_channel)) {
                $data[$k]['channels'] = [];
                continue;
            }

            $channel_title = [];
            foreach ($project_channel as $kk => $vv) {
                $channel_title[] = $vv->channel->title;
            }
            $data[$k]['channels'] = $channel_title;
            unset($data[$k]['projectChannel']);
            unset($data[$k]['project_channel']);

        }


        $result = ['total' => $list->total(), 'rows' => $data];
        return json($result);
    }


    public function add()
    {
        if (false === $this->request->isPost()) {
            $channelList = Channel::where('status', Channel::STATUS_NORMAL)->select();
            $this->view->assign('channelList', $channelList);
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
            // channel关联
            $channel = $params['channel'];
            $project_channel = [];
            foreach ($channel as $k => $v) {
                $project_channel[] = [
                    'project_id' => $this->model->id,
                    'channel_id' => $v,
                ];
            }

            $this->model->projectChannel()->saveAll($project_channel);

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
            $row->projectChannel;
            $channelArr = [];
            foreach ($row->projectChannel as $k => $v) {
                $channelArr[] = $v->channel_id;
            }
            $row = $row->toArray();
            $row['channel'] = $channelArr;
            $this->view->assign('channelList', build_select('row[channel][]', Channel::where('status', 1)->column('id,title'), $row['channel'], ['class' => 'form-control selectpicker', 'multiple' => 'multiple']));
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
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            // channel关联
            $channel = $params['channel'];
            $project_channel = [];
            foreach ($channel as $k => $v) {
                $project_channel[] = [
                    'project_id' => $row->id,
                    'channel_id' => $v,
                ];
            }
            $row->projectChannel()->delete();
            $row->projectChannel()->saveAll($project_channel);
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

    /**
     * delete
     */
    public function del($ids = "")
    {
        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }

        Db::startTrans();
        try {
            $this->model->where('id', $ids)->update(['status' => ProjectModel::STATUS_DELETE]);
            ProjectChannel::where('project_id', $ids)->delete();
            Db::commit();
        }catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        // 写入缓存
        $this->setCache($ids);
        $this->success();
    }

    /**
     * getChannelListByProjectId
     */
    public function getChannelListByProjectId()
    {
        $project_id = $this->request->get('project_id');
        $projectChannel = ProjectChannel::where('project_id', $project_id)->with('channel')->select();
        $data = [];
        foreach ($projectChannel as $k => $v) {
            $data[] = [
                'id' => $v->channel->id,
                'name' => $v->channel->title,
            ];
        }
        return json(['code' => 1, 'data' => $data]);
    }

    /**
     * 写入缓存
     */
    protected function setCache($id)
    {
        $cacheKey = ProjectModel::CACHE_KEY .$id;
        // 删除缓存
        cache($cacheKey, null);

        $cacheData = $this->model->where('id', $id)
            ->with(['projectChannel'])
            ->where('status', ProjectModel::STATUS_NORMAL)
            ->find();

        if ($cacheData) {
            $cacheData = $cacheData->toArray();
            cache($cacheKey, $cacheData);
        }
    }
}