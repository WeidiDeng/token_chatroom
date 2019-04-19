<?php
namespace app\chatroom\validate;

use think\Validate;
use think\Db;

class Chatroom extends Validate
{
    protected $rule = [
        'chatroom_name' => 'require|max:25|checkUnique',
        'chatroom_max' => 'number|between:2,25',
    ];
    protected $message = [
        'chatroom_name.require' => '聊天室名不能为空',
        'chatroom_name.max' => '聊天室名最多不能超过25个字符',
        'chatroom_max.number' => '聊天室最大人数必须是数字',
        'chatroom_max.between' => '聊天室最大人数只能在2-25之间',
    ];
    protected function checkUnique($value,$rule,$data=[])
    {
        $res = Db::table("chatroom")
            ->where("name", $value)
            ->where("count", ">", 0)
            ->count();
        return $res == 0 ? true : '聊天室名不能重复';
    }
}