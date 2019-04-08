<?php
namespace app\index\controller;

use app\index\model\Blacklist;
use app\index\model\ChatHistory;
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
        // 更新cookie和session有效时常
        $sessid = cookie('PHPSESSID');
        cookie('PHPSESSID', $sessid, 300);
        $res = OnlineUser::get(['sess_id'=>$sessid]);
        $chatroom = Session::get("current_chat");
        $res->chatroom_hash = $chatroom;
        $res->save();

        if ($chatroom) {
            $ignoredAction = ["room", "to_index", "logout", "join_room", "post_message", "get_message"];
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
        $chat = Session::get("current_chat");
        if ($chat) {
            ChatHistory::create([
                "chatroom_id"=>ChatroomModel::get(["hash"=>$chat])->id,
                "name"=>Session::get("logged_name"),
                "type"=>0,
                "content"=>Session::get("logged_name")."已离开聊天室"
            ]);
            ChatroomModel::get(['hash'=>$chat])->setDec("count");
        }
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
        $res = ChatroomModel::get(['hash'=>$hash]);
        if (empty($res)) {
            $this->error("聊天室不存在");
        }
        if ($res->count == $res->max_count) {
            $this->error("聊天室已满");
        }
        $old_hash = Session::get("current_chat");
        if ($old_hash && $old_hash!=$hash) {
            ChatHistory::create([
                "chatroom_id"=>ChatroomModel::get(["hash"=>$old_hash])->id,
                "name"=>Session::get("logged_name"),
                "type"=>0,
                "content"=>Session::get("logged_name")."已离开聊天室"
            ]);
            ChatroomModel::get(['hash'=>$old_hash])->setDec("count");
        }
        Session::set("current_chat", $hash);
        ChatHistory::create([
            "chatroom_id"=>ChatroomModel::get(["hash"=>$hash])->id,
            "name"=>Session::get("logged_name"),
            "type"=>0,
            "content"=>Session::get("logged_name")."已加入聊天室"
        ]);
        $res->setInc("count");
        $this->redirect("chatroom/room", ["hash"=>$hash]);
    }
    // 退出聊天室
    public function to_index()
    {
        $old_hash = Session::get("current_chat");
        if ($old_hash) {
            ChatHistory::create([
                    "chatroom_id"=>ChatroomModel::get(["hash"=>$old_hash])->id,
                    "name"=>Session::get("logged_name"),
                    "type"=>0,
                    "content"=>Session::get("logged_name")."已离开聊天室"
            ]);
            ChatroomModel::get(['hash'=>$old_hash])->setDec("count");
        }
        Session::delete("current_chat");
        $this->redirect("chatroom/index");
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
        $chatroom = Session::get("current_chat");
        if (!$chatroom) {
            return json(array("code"=>-1,"msg"=>"目前没有加入聊天室"));
        }

        // 关键字检测
        $black_list = Blacklist::all()->column("content");
        foreach ($black_list as $black_word) {
            if (strpos($message, $black_word)!==false) {
                return json(array("code"=>-1,"msg"=>"敏感词"));
            }
        }

        $chatroom_id = ChatroomModel::get(["hash"=>$chatroom])->id;
        $logged_name = Session::get("logged_name");
        ChatHistory::create([
            "chatroom_id"=>$chatroom_id,
            "name"=>$logged_name,
            "content"=>$message,
        ]);
        return json(array("code"=>0,"msg"=>"ok"));
    }
    // 获取消息
    public function get_message()
    {
        $chatroom = Session::get("current_chat");
        if (!$chatroom) {
            return json(array("code"=>-1,"msg"=>"目前没有加入聊天室"));
        }
        $cnt = input("get.cnt/d");
        if (empty($cnt)) {
            $cnt = 0;
        }
        $chatroom_id = ChatroomModel::get(["hash"=>$chatroom])->id;
        $history = ChatHistory::all(["chatroom_id"=>$chatroom_id])->where("id", ">", $cnt);
        return json(array("code"=>0,"msg"=>$history));
    }
}