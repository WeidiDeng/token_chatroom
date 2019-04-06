<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Session;

use app\index\model\OnlineUser;
use app\index\model\Chatroom as ChatroomModel;

class Chatroom extends Controller
{
    protected function initialize()
    {
        if (!Session::get("logged_name")) {
            $this->redirect(url("index/index"));
        }
        $sessid = cookie('PHPSESSID');
        cookie('PHPSESSID', $sessid, 300);
        $res = OnlineUser::get(['sess_id'=>$sessid]);
        $res->save();
        if (Session::get("current_chat")) {
            $ignoredAction = ["room", "to_index", "logout", "join_room"];
            if (!in_array(request()->action(), $ignoredAction)) {
                $this->redirect("chatroom/room", ["hash"=>Session::get("current_chat")]);
            }
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
    public function create_chatroom()
    {
        $chatroom_name = input("post.chatroom_name");
        $chatroom_max = input("post.chatroom_max");
        if (empty($chatroom_name)) {
            $this->error("聊天室名不能为空");
        }
        if (empty($chatroom_max)) {
            $this->error("聊天室最大人数不能为空");
        }
        if (ChatroomModel::get(['name'=>$chatroom_name])) {
            $this->error("聊天室名不能重复");
        }
        ChatroomModel::create([
            "name" => $chatroom_name,
            "max_count"=>$chatroom_max
        ]);
        Session::set("current_chat", ChatroomModel::get(['name'=>$chatroom_name])->hash);
        $this->redirect("index/chatroom/room", ["hash"=>Session::get("current_chat")]);
    }
    // 进入聊天室
    public function room($hash)
    {
        if (!Session::get("current_chat")) {
            $this->redirect("chatroom/index");
        }
        if (Session::get("current_chat")!=$hash) {
            $this->redirect("chatroom/room", ["hash"=>Session::get("current_chat")]);
        }
        $this->assign('user_name', Session::get("logged_name"));
         $this->assign("chatroom_name",ChatroomModel::get(["hash"=>$hash])->name);
        return $this->fetch("chatroom");
    }
    // 加入聊天室
    public function join_room($hash)
    {
        if (Session::get("current_chat")) {
            Session::delete("current_chat");
        }
        Session::set("current_chat", $hash);
        $this->redirect("chatroom/room", ["hash"=>$hash]);
    }
    // 退出聊天室
    public function to_index()
    {
        Session::delete("current_chat");
        $this->redirect("chatroom/index");
    }
    public function list_chatroom()
    {
        $res = ChatroomModel::all();
        return json($res);
    }
}