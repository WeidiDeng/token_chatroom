<?php
namespace app\index\controller;

use app\index\model\Ip;
use app\index\model\OnlineUser;
use think\Controller;
use think\facade\Session;
// +----------------------------------------------------------------------
// 首先判断用户Session是否结束，跳转到对应的聊天室或聊天室列表
// 没有Session就跳至登录页
// 登录页判断用户ip是否封禁
// +----------------------------------------------------------------------
class Index extends Controller
{
    protected function initialize()
    {
        if (Session::get("logged_name")) {
            if (Session::get("current_chat")) {
                $this->redirect("index/chatroom/room");
            } else {
                $this->redirect(url("index/chatroom/index"));
            }
        }
    }

    public function index()
    {
//        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
        return $this->fetch('index');
    }
    public function checkip()
    {
        $param = input('post.');
        if(empty($param['name'])){
            $this->error('昵称不能为空');
        }
        $ip = new Ip();
        $result = $ip::get(["ip"=>request()->ip()]);
        if (empty($result)||strtotime($result->block_end)<time()) {
            Session::set("logged_name", $param['name']);
            OnlineUser::create([
                'sess_id'=>cookie('PHPSESSID'),
                'name'=>$param['name']
            ], ['sess_id', 'name'], true);
            $this->success("登陆成功", url("index/chatroom/index"));
        } else {
            $this->error("你ip被封禁，请联系管理员解禁");
        }
    }
    public function blockip()
    {
        $res = Ip::get(["ip"=>request()->ip()]);
        if (strtotime($res->block_end)>time()) {
            return $res->ip."已被封禁";
        }
        $res->block_end=date('Y-m-d H:i:s', time()+300);
        $res->block_count++;
        $res->save();
        return $res->ip."被封禁";
    }

//    public function hello($name = 'ThinkPHP5')
//    {
//        return 'hello,' . $name;
//    }
}
