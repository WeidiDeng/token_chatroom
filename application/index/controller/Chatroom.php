<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Session;

class Chatroom extends Controller
{
    public function index() {
        if (!Session::get("logged_name")) {
            $this->redirect(url("index/index/index"));
        } else if (Session::get("current_chat")) {
            $this->redirect(url("index/chatroom/".Session::get("current_chat")));
        } else {
            $this->assign('user_name', Session::get("logged_name"));
            return $this->fetch("index");
        }
    }
    public function logout() {
        if (!Session::get("logged_name")) {
            $this->redirect(url("index/index/index"));
        } else {
            Session::clear();
            $this->success("退出登陆成功",url("index/index/index"));
        }
    }
}