<?php
namespace app\index\model;

use think\Model;

class OnlineUser extends Model
{
    protected $auto = ['last_active'];
    protected function setLastActiveAttr()
    {
        return date('Y-m-d H:i:s',time());
    }
}