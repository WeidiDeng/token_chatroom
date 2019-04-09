$(function () {
    var cnt = 0
    function show_message() {
        $.ajax({
            url:"/chatroom/get_message",
            type:"get",
            dataType:"json",
            data: {
              cnt: cnt
            },
            xhrFields:{
                withCredentials:true
            },
            success:function(data){
                $.each(data.msg, function (index, value) {
                    cnt=value.id
                    if (value.type===1) {
                        $("#messageList").prepend("<li class=\"list-group-item\">\n" +
                            "                <div>\n" +
                            "                    <div class=\"float-left\">\n" +
                            "                        <img src=\"/static/img/avatar.jpeg\" alt=\"头像\">\n" +
                            "                        "+ value.name +"\n" +
                            "                    </div>\n" +
                            "                    <div class=\"text-center\">\n" +
                            "                        "+ value.content +"\n" +
                            "                    </div>\n" +
                            "                    <div class=\"float-right\">\n" +
                            "                        "+ value.sent_time +"\n" +
                            "                    </div>\n" +
                            "                </div>\n" +
                            "            </li>")
                    } else if (value.type===0) {
                        $("#messageList").prepend("<li class=\"list-group-item\">\n" +
                            "                <div>\n" +
                            "                    <div class=\"float-left\">\n" +
                            "                        系统信息\n" +
                            "                    </div>\n" +
                            "                    <div class=\"text-center\">\n" +
                            "                        "+ value.content +"\n" +
                            "                    </div>\n" +
                            "                    <div class=\"float-right\">\n" +
                            "                        "+ value.sent_time +"\n" +
                            "                    </div>\n" +
                            "                </div>\n" +
                            "            </li>")
                    }
                })
            }
        })
    }
    $("#post_message>button").click(function () {
        var post_data = $('#post_message').serialize();
        $.ajax({
            url:"/chatroom/post_message",
            type:"POST",
            dataType:"json",
            data: post_data,
            xhrFields:{
                withCredentials:true
            },
            success:function (data) {
                if (data.code!=0) {
                    alert(data.msg)
                } else {
                    show_message()
                    $("#post_message textarea").val("")
                }
            }
        })
    })
    show_message()
    window.setInterval(show_message, 1000)
})