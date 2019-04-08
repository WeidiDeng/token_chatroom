<?php
namespace app\chatroom\controller;

use app\chatroom\model\Blacklist;
use app\chatroom\model\ChatHistory;
use think\Controller;
use think\facade\Session;

use app\chatroom\model\OnlineUser;
use app\chatroom\model\Chatroom as ChatroomModel;

class Chatroom extends Controller
{
    protected $beforeActionList = [
        "check_login",
        "check_chatroom"=>['only'=>'room,post_message,get_message'],
        "redirect_to_room"=>['only'=>'index,create_chatroom,'],
        "leave_chatroom"=>['only'=>'logout,join_room,to_index']
    ];

    protected function check_login()
    {
        if (!Session::get("logged_name")) {
            $this->redirect(url("/"));
        }
        // 更新cookie和session有效时常
        $sessid = cookie('PHPSESSID');
        cookie('PHPSESSID', $sessid, 300);
        $res = OnlineUser::get(['sess_id'=>$sessid]);
        $chatroom = Session::get("current_chat");
        $res->chatroom_id = $chatroom;
        $res->save();
    }

    protected function check_chatroom()
    {
        if (!Session::get("current_chat")) {
            $this->redirect("chatroom/index/");
        }
    }

    protected function redirect_to_room()
    {
        $chatroom = Session::get("current_chat");
        if ($chatroom) {
            $this->redirect("room", ["id"=>$chatroom]);
        }
    }

    protected function leave_chatroom()
    {
        $chat = Session::get("current_chat");
        if ($chat) {
            ChatHistory::create([
                "chatroom_id"=>$chat,
                "name"=>Session::get("logged_name"),
                "type"=>0,
                "content"=>Session::get("logged_name")."已离开聊天室"
            ]);
            ChatroomModel::get(['id'=>$chat])->setDec("count");
        }
    }
    public function index() {
        $this->assign('user_name', Session::get("logged_name"));
        return $this->fetch("index");
    }
    public function logout() {
        Session::clear();
        cookie('PHPSESSID',null);
        $this->success("退出登陆成功",url("/"));
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
        $dup_query = ChatroomModel::where([
            ['name','eq' ,$chatroom_name],
            ['count', '>', 0]
        ])->select();
        if (count($dup_query)>0) {
            $this->error("聊天室名不能重复");
        }
        ChatroomModel::create([
            "name" => $chatroom_name,
            "max_count"=>$chatroom_max
        ]);
        $chatroom = ChatroomModel::where([
            ['name','eq' ,$chatroom_name],
            ['count', '>', 0]
        ])->find();
        Session::set("current_chat", $chatroom->id);
        $this->redirect("/chatroom/room/".$chatroom->id);
    }
    // 进入聊天室
    public function room($id)
    {
        $old_id = Session::get("current_chat");
        if ($old_id!=$id) {
            $this->redirect("chatroom/room", ["id"=>$old_id]);
        }
        $this->assign('user_name', Session::get("logged_name"));
        $this->assign("chatroom_name",ChatroomModel::get(["id"=>$id])->name);
        return $this->fetch("chatroom");
    }
    // 加入聊天室
    public function join_room($id)
    {
        $res = ChatroomModel::get(['id'=>$id]);
        if (empty($res)) {
            $this->error("聊天室不存在");
        }
        if ($res->count == $res->max_count) {
            $this->error("聊天室已满");
        }
        Session::set("current_chat", $id);
        ChatHistory::create([
            "chatroom_id"=>$id,
            "name"=>Session::get("logged_name"),
            "type"=>0,
            "content"=>Session::get("logged_name")."已加入聊天室"
        ]);
        $res->setInc("count");
        $this->redirect("chatroom/room/".$id);
    }
    // 退出聊天室
    public function to_index()
    {
        Session::delete("current_chat");
        $this->redirect("chatroom/chatroom/index");
    }
    // 列出聊天室
    public function list_chatroom()
    {
        $res = ChatroomModel::all()->where("count",">", 0);
        return json($res);
    }
    // 发送消息
    public function post_message()
    {
        $message = input("post.message");
        if (empty($message)) {
            return json(array("code"=>-1,"msg"=>"必须发送消息"));
        }
        // 关键字检测
        $black_list = Blacklist::all()->column("content");
        foreach ($black_list as $black_word) {
            if (strpos($message, $black_word)!==false) {
                return json(array("code"=>-1,"msg"=>"敏感词"));
            }
        }

        $chatroom = Session::get("current_chat");
        $logged_name = Session::get("logged_name");
        ChatHistory::create([
            "chatroom_id"=>$chatroom,
            "name"=>$logged_name,
            "content"=>$message,
        ]);
        return json(array("code"=>0,"msg"=>"ok"));
    }
    // 获取消息
    public function get_message()
    {
        $chatroom = Session::get("current_chat");
        $cnt = input("get.cnt/d");
        if (empty($cnt)) {
            $cnt = 0;
        }
        $history = ChatHistory::all(["chatroom_id"=>$chatroom])->where("id", ">", $cnt);
        return json(array("code"=>0,"msg"=>$history));
    }
}