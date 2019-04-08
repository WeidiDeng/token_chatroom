<?php
namespace app\chatroom\model;

use think\Model;

class Chatroom extends Model
{
    protected $auto = ["last_active"];
    protected function setLastActiveAttr()
    {
        return date('Y-m-d H:i:s',time());
    }
}