<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use app\chatroom\model\Ip;
use think\Controller;
use think\facade\Session;
use think\Db;

class Index extends Controller
{
    protected $beforeActionList = [
        'check_login'=>["except"=>'login,do_login']
    ];
    protected function check_login()
    {
        if (!Session::get('logged_in_admin_name')) {
            $this->redirect('/admin/');
        }
    }

    public function index()
    {
        $ip_list = Ip::all();
        $this->assign("ip_list", $ip_list);
        return $this->fetch("index");
    }

    public function login()
    {
        if (Session::get('logged_in_admin_name')) {
            $this->redirect(url('admin/index/'));
        }
        return view("login");
    }
    public function do_login()
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
            $this->success("登陆成功",url("admin/index/"));
        } else {
            $this->error('密码错误');
        }
    }
    public function logout()
    {
        Session::clear();
        cookie("PHPSESSID",null);
        $this->success("注销成功，3秒后跳至登录页",'/admin/');
    }
    public function unblock($id)
    {
        Db::name('ip')
            ->where('id', $id)
            ->update(['block_end' => null]);
        $this->redirect('/admin/index');
    }
}