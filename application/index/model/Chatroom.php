<?php
namespace app\index\model;

use think\Model;

class Chatroom extends Model
{
    protected $insert = ['hash'];
    protected function setHashAttr()
    {
        return substr(str_shuffle(md5(time())),0,8);
    }
}