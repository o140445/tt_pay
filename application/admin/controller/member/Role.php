<?php

namespace app\admin\controller\member;

use app\common\controller\Backend;
use app\common\model\MemberRole;

/**
 * 商家角色管理
 *
 * @icon fa fa-circle-o
 */
class Role extends Backend
{

    /**
     * Role模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MemberRole();

    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function add()
    {
        $this->error('不允许添加');
    }

    public function del($ids = null)
    {
        $this->error('不允许删除');
    }


    public function multi($ids = null)
    {
        $this->error('不允许批量操作');
    }

    public function recyclebin()
    {
        $this->error('不允许回收站操作');
    }

    public function restore($ids = null)
    {
        $this->error('不允许还原');
    }

    public function destroy($ids = null)
    {
        $this->error('不允许删除');
    }


}
