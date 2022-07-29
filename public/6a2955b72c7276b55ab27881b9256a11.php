<?php /*a:1:{s:35:"../app/view/admin/product_page.html";i:1658503919;}*/ ?>
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
    
    <table id="dg" title="产品管理" class="easyui-datagrid" style="width:100%;height:auto"
            url="productlist"
            toolbar="#toolbar"
            rownumbers="true" fitColumns="true" fit="true" singleSelect="true">
        <thead>
            <tr>
                <th field="id" width="50">Id</th>
                <th field="names" width="50">产品名</th>
                <th field="guige" width="50">规格</th>
                <th field="danwei" width="50">单位</th>
                <th field="danjia" width="50">单价</th>
                <th field="paixu" width="50">排序</th>
                <th field="xiugai" width="50" hidden="true">修改</th>
            </tr>
        </thead>
    </table>
    <div id="toolbar">
        <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="pro_add()">新增</a> 

        <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="pro_edit()">修改</a> 

        <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="pro_del()">删除</a>

        <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="pro_paixu(1)">排序上升</a>

        <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="pro_paixu(2)">排序下降</a>
        
        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="tj()">查看筛选内容的统计信息</a> -->

        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">删除</a> -->
        
    </div>

    <div id="win">
        <p id="win_p_1">产品详情</p>
        产品名称:<input style="width:300px" id="win_p_names" /><br />
        <!-- <input type="hidden" id="win_p_id" /> -->
        规格<input  style="width:300px" id="win_p_guige"><br />
        单位<input  style="width:300px" id="win_p_danwei"><br />
        单价<input  style="width:300px" id="win_p_danjia"><br />
        <button id="win_p_button">确定</button>

    </div>





    <script>

    (function(){//初始化详细信息面板
        $('#win').window({
            width:600,
            height:400,
            modal:true,
            title:"产品"
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
                
                names:$("#win_p_names").val(),
                guige:$("#win_p_guige").val(),
                danwei:$("#win_p_danwei").val(),
                danjia:$("#win_p_danjia").val()
            };
            func(dat);
            $("#win").window('close');
        });
    }

    function pro_edit(did){//修改，吊起一个窗口
        var row = $('#dg').datagrid('getSelected');
        if (row){
            $("#win_p_names").val(row.names);
            // $("#win_p_id").val(row.id);
            $("#win_p_guige").val(row.guige);
            $("#win_p_danwei").val(row.danwei);
            $("#win_p_danjia").val(row.danjia);
            var ids = row.id;
            wins(function(r){
                if (r){
                    r.id = ids;
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
    function pro_add(){
        wins(function(r){
            if(r){
                $.post('productadd',r,function(res){
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
    function pro_del(){
        //productdel
        var row = $('#dg').datagrid('getSelected');

        if(row.xiugai==1){
            alert("该项与材料计算规则绑定，不可删除");
            return;
        }

        if (row){
            $.messager.confirm('提示','确认删除吗?',function(r){
                if (r){
                    $.post('productdel',{id:row.id},function(res){
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