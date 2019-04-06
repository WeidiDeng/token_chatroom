<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Session;

use app\index\model\OnlineUser;

class Chatroom extends Controller
{
    protected function initialize()
    {
        if (!Session::get("logged_name")) {
            $this->redirect(url("index/index/index"));
        }
        $sessid = cookie('PHPSESSID');
        cookie('PHPSESSID', $sessid, 300);
        $res = OnlineUser::get(['sess_id'=>$sessid]);
        $res->save();
        if (Session::get("current_chat")) {
            $this->redirect(url("index/chatroom/".Session::get("current_chat")));
        }
    }
    public function index() {
        $this->assign('user_name', Session::get("logged_name"));
        return $this->fetch("index");
    }
    public function logout() {
        Session::clear();
        $this->success("退出登陆成功",url("index/index/index"));
    }
    public function createChatroom()
    {
        
    }
}