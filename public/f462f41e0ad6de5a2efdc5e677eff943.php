<?php /*a:1:{s:33:"../app/view/admin/zhuwa_page.html";i:1657956210;}*/ ?>
<!--主瓦尺寸管理-->
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
    
    <table id="dg" title="主瓦管理" class="easyui-datagrid" style="width:100%;height:auto"
            url="zhuwa_list"
            toolbar="#toolbar"
            rownumbers="true" fitColumns="true" fit="true" singleSelect="true">
        <thead>
            <tr>
                <th field="id" width="50" hidden="true">Id</th>
                <th field="chicun" width="50">长度</th>
                <th field="boshu" width="50">波数</th>
            </tr>
        </thead>
    </table>
    <div id="toolbar">
        <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="cc_add()">新增</a> 
        <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="cc_del()">删除</a>

        
        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="tj()">查看筛选内容的统计信息</a> -->

        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">删除</a> -->
        
    </div>

    <div id="win">
        <!-- <p id="win_p_1">主瓦尺寸新增</p> -->
        尺寸<input  style="width:300px" id="win_p_chicun"><br />
        <button id="win_p_button">确定</button>

    </div>


    <script>

    (function(){//初始化详细信息面板
        $('#win').window({
            width:600,
            height:400,
            modal:true,
            title:"增加主瓦尺寸"
            });
        $('#win').window('close');
    })();

    //创建动态修改窗口
    function wins(func){
        // alert(111);
        $("#win").window('open');
        $('#win_p_button').unbind();
        $('#win_p_button').bind('click',function(){
            var dat = {
                chicun:$("#win_p_chicun").val(),
                // names:$("#win_p_names").val(),
                // guige:$("#win_p_guige").val(),
                // danwei:$("#win_p_danwei").val(),
                // danjia:$("#win_p_danjia").val()
            };
            func(dat);
            $("#win").window('close');
        });
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