<?php /*a:1:{s:30:"../app/view/qiantai/index.html";i:1657606658;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <title>前台登录</title>
        <script type="text/javascript" src="/static/js/jquery.min.js"></script>
        <script src="https://cdn.bootcss.com/blueimp-md5/2.10.0/js/md5.js"></script>
    </head>
    <body>
        <form onsubmit="return false">
            <p style="font-size: 2em" align="center">登录</p>
            <input type="text" placeholder="用户名" required="">
            <input type="password" class="mail" placeholder="密码" required="">
            <input type="button" value="登录">
        </form>
        <script>
            (function(){
                // alert("aaa");
                var x = document.getElementsByTagName("input");
                console.log(x);
                x.item(2).onclick=function(){
                    var pass1 = md5(x.item(1).value);
                    //alert(pass1);
                    $.ajax({url:"login",method:"post",data:{"uname":x[0].value,"password":pass1},success:function(res){
                        //alert(res.info);
                        if(res.code != 0){
                            window.location.href = "admin";
                        }else{
                            alert("用户名密码错误");
                        }
                    }});
                    return false;
                }
            })();
        </script>
    </body>
    
</html>