<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use think\Controller;
use think\facade\Session;

class Index extends Controller
{
    public function index()
    {
        if (!Session::get('logged_in_admin_name')) {
            $this->error("你没有登陆", url('admin/index/login'));
        }
        return Session::get('logged_in_admin_name')."已登录";
    }

    public function login()
    {
        if (Session::get('logged_in_admin_name')) {
            $this->redirect(url('admin/index/index'));
        }
        return view("login");
    }
    public function checklogin()
    {
        $param = input('post.');
        if(empty($param['email'])){
            $this->error('邮箱不能为空');
        }

        if(empty($param['password'])){
            $this->error('密码不能为空');
        }

        $admin = Admin::get(["email"=>$param['email']]);
        if (empty($admin)) {
            $this->error('邮箱未注册');
        }
        else if (md5($param['password'])==$admin->password) {
            Session::set("logged_in_admin_name", $admin->name);
            $this->success("登陆成功",url("admin/index/index"));
        } else {
            $this->error('密码错误');
        }
    }
    public function logout()
    {
        Session::clear();
        $this->success("注销成功，3秒后跳至登录页",url('admin/index/login'));
    }
}