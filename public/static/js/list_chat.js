$(function () {
    function list_chat() {
        $.ajax({
            url:"/chatroom/list_chatroom",
            type:"GET",
            dataType:"json",
            xhrFields:{
                withCredentials:true
            },
            success:function(data){
                $(".container .list-group").empty();
                $.each(data, function (index, value) {
                    $(".container .list-group").append("<li class=\"list-group-item\">\n" +
                        "                <div>\n" +
                        "                    <a href='/chatroom/join_room/"+ value.id +"' >"+ value.name + "</a>\n" +
                        "                    <div class=\"float-right\">\n" +
                        "                            "+ value.count + "/"+value.max_count+"\n" +
                        "                    </div>\n" +
                        "                </div>\n" +
                        "            </li>")
                })
            }
        })
    }
    list_chat();
    $(".dropdown-item:first-child").click(list_chat);
    window.setInterval(
        list_chat,
        5000
    )
})