<?php
namespace app\index\model;

use think\Model;

class Ip extends Model
{
    protected $auto = ['ip'];

    protected function setIpAttr()
    {
        return request()->ip();
    }
}