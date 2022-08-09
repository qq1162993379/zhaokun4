<?php /*a:1:{s:35:"../app/view/admin/user_qt_page.html";i:1660046389;}*/ ?>
<!--前台账户-->
<!DOCTYPE html>
<head>
    <link rel="stylesheet" type="text/css" href="/static/css/easyui/easyui.css">
    <link rel="stylesheet" type="text/css" href="/static/css/easyui/icon.css">
    <!-- <link rel="stylesheet" type="text/css" href="__CSS__/myStyle.css"> -->
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="/static/js/easyui-lang-zh_CN.js"></script>
    <script src="https://cdn.bootcss.com/blueimp-md5/2.10.0/js/md5.js"></script>
    
</head>
<body style="padding:0px;">
    
    <table id="dg" title="销售账户" class="easyui-datagrid" style="width:100%;height:auto"
            url="user_qt_list"
            toolbar="#toolbar"
            rownumbers="true" fitColumns="true" fit="true" singleSelect="true">
        <thead>
            <tr>
                <th field="id" width="50">Id</th>
                <th field="username" width="50">账号</th>
                <!-- <th field="tel" width="50">电话</th>
                <th field="opid" width="50">微信opid</th> -->
            </tr>
        </thead>
    </table>
    <div id="toolbar">
        <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="pass_edit()">修改密码</a> 
        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="cc_del()">删除</a> -->

        
        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="tj()">查看筛选内容的统计信息</a> -->

        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">删除</a> -->
        
    </div>

    <div id="win">

        新密码<input  style="width:300px" id="win_p_chicun"><br />
        <button id="win_p_button">确定</button>

    </div>


    <script>

    (function(){//初始化详细信息面板
        $('#win').window({
            width:600,
            height:400,
            modal:true,
            title:"修改密码"
            });
        $('#win').window('close');
    })();

    //创建动态修改窗口
    function wins(func){
        // alert(111);
        $("#win").window('open');
        $('#win_p_button').unbind();
        $('#win_p_button').bind('click',function(){
            // var dat = {
            //     passwords:md5($("#win_p_chicun").val()),
                // names:$("#win_p_names").val(),
                // guige:$("#win_p_guige").val(),
                // danwei:$("#win_p_danwei").val(),
                // danjia:$("#win_p_danjia").val()
            // };
            var dat = md5($("#win_p_chicun").val());
            func(dat);
            $("#win").window('close');
        });
    }
    function pass_edit(){
        var row = $('#dg').datagrid('getSelected');
        if (row){
            wins(function(r){
                if(r){
                    $.post('user_qt_editpass',{passwords:r,id:row.id},function(res){
                        if(res.code != 1){
                            $.messager.show({	// show error message
                                title: 'Error',
                                msg: res.msg,
                                showSpeed:2000
                            });
                        }else{
                            $.messager.show({	// show error message
                                title: 'Success',
                                msg: res.msg,
                                showSpeed:2000
                            });
                            // $('#dg').datagrid('reload');
                        }
                    });
                }
            });
        }else{
            alert("未选择用户");
        }
        
    }
    function cc_add(){//主瓦尺寸新增
        wins(function(r){
            if(r){
                $.post('zhuwa_chicun_add',r,function(res){
                    if(res.code != 1){
                        $.messager.show({	// show error message
                            title: 'Error',
                            msg: res.msg,
                            showSpeed:2000
                        });
                    }else{
                        $.messager.show({	// show error message
                            title: 'Success',
                            msg: res.msg,
                            showSpeed:2000
                        });
                        $('#dg').datagrid('reload');
                    }
                });
            }
        });
    }
    function cc_del(){//主瓦尺寸删除
        var row = $('#dg').datagrid('getSelected');
        if (row){
            $.messager.confirm('提示','确认删除吗?',function(r){
                if (r){
                    $.post('zhuwa_chicun_del',{id:row.id},function(res){
                        if(res.code != 1){
                            $.messager.show({	// show error message
                                title: 'Error',
                                msg: res.msg,
                                showSpeed:2000
                            });
                        }else{
                            $.messager.show({	// show error message
                                title: 'Success',
                                msg: res.msg,
                                showSpeed:2000
                            });
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            });
        }
    }

    function pro_edit(){//修改，吊起一个窗口
        var row = $('#dg').datagrid('getSelected');
        if (row){
            $("#win_p_names").html(row.names);
            $("#win_p_id").val(row.id);
            $("#win_p_guige").val(row.guige);
            $("#win_p_danwei").val(row.danwei);
            $("#win_p_danjia").val(row.danjia);
            wins(function(r){
                if (r){
                    $.post('productupdate',r,function(res){
                        if(res.code != 1){
                            $.messager.show({	// show error message
                                title: 'Error',
                                msg: res.msg,
                                showSpeed:2000
                            });
                        }else{
                            $.messager.show({	// show error message
                                title: 'Success',
                                msg: res.msg,
                                showSpeed:2000
                            });
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            });
        }else{
            $.messager.show({	// show error message
                title: 'Error',
                msg: "未选择任何一个产品",
                showSpeed:2000
            });
        }
    }
    function pro_paixu(a){
    //productpaixu
        var row = $('#dg').datagrid('getSelected');
        if(row){
            $.post('productpaixu',{id:row.id,paixu:row.paixu,caozuo:a},function(res){
                if(res.code != 1){
                    $.messager.show({	// show error message
                        title: 'Error',
                        msg: res.msg,
                        showSpeed:2000
                    });
                }else{
                    $.messager.show({	// show error message
                        title: 'Success',
                        msg: res.msg,
                        showSpeed:2000
                    });
                    $('#dg').datagrid('reload');
                }
            });
        }else{
            $.messager.show({	// show error message
                title: 'Error',
                msg: "未选择任何一个产品",
                showSpeed:2000
            });
        }
        
    }



    function destroyUser(){
        var row = $('#dg').datagrid('getSelected');
        if (row){
            $.messager.confirm('提示','确认删除吗?',function(r){
                if (r){
                    $.post('{:U(del_tg)}',{id:row.Id},function(result){
                        var result = eval('('+result+')');
                        if (!result.errorMsg){
                            $('#dg').datagrid('reload');	// reload the user data
                        } else {
                            $.messager.show({	// show error message
                                title: 'Error',
                                msg: result.errorMsg
                            });
                        }
                    },'json');
                }
            });
        }
    }
    function tg(){
        var row = $('#dg').datagrid('getSelected');
        if (row){
            $.messager.confirm('提示','是否进入修改界面',function(r){
                if (r){
                    var forms=document.createElement("form");
                    forms.action="{:U(xiugai_tg)}";
                    var inps=document.createElement("input");
                    inps.name='id';
                    inps.value=row.id;
                    forms.appendChild(inps);
                    forms.method="post";
                    forms.submit();
                }
            });
        }
    }
    </script>
    </body>
</body>