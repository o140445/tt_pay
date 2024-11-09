<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        echo  json_encode([
            'code' => 200,
            'msg' => '非法请求',
        ]);
//        return
    }

}
