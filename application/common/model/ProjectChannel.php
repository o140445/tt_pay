<?php

namespace app\common\model;

use think\Model;

class ProjectChannel extends  Model
{
    protected $name = 'project_channel';


    // channel
    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', 'id');
    }
}