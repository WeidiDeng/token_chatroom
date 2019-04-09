<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//Route::get('think', function () {
//    return 'hello,ThinkPHP5!';
//});
//
//Route::get('hello/:name', 'index/hello');
Route::get('/', 'chatroom/index/index');
Route::post('checkip', 'chatroom/index/checkip');
Route::get('chatroom/index', 'chatroom/chatroom/index');
Route::get('chatroom/to_index', 'chatroom/chatroom/to_index');
Route::get('chatroom/list_chatroom', 'chatroom/chatroom/list_chatroom');
Route::get('chatroom/logout', 'chatroom/chatroom/logout');
Route::get('chatroom/join_room/:id', 'chatroom/chatroom/join_room');
Route::post('chatroom/create_chatroom', 'chatroom/chatroom/create_chatroom');

Route::get('chatroom/room/:id', 'chatroom/chatroom/room');
Route::get('chatroom/get_message', 'chatroom/chatroom/get_message');
Route::post('chatroom/post_message', 'chatroom/chatroom/post_message');

Route::get('admin/', 'admin/index/login');
Route::get('admin/index', 'admin/index/index');
Route::post('admin/do_login', 'admin/index/do_login');
Route::get('admin/logout', 'admin/index/logout');
Route::get('admin/unblock/:id', 'admin/index/unblock');

return [

];
