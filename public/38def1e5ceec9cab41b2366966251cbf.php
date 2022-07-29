<?php /*a:1:{s:39:"../app/view/admin/dingdan_new_list.html";i:1658975808;}*/ ?>
<!DOCTYPE html>
<head>
    <link rel="stylesheet" type="text/css" href="/static/css/easyui/easyui.css">
    <link rel="stylesheet" type="text/css" href="/static/css/easyui/icon.css">
    <!-- <link rel="stylesheet" type="text/css" href="__CSS__/myStyle.css"> -->
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="/static/js/easyui-lang-zh_CN.js"></script>
    <script src="https://cdn.bootcss.com/blueimp-md5/2.10.0/js/md5.js"></script>
    <style type="text/css">
        .ddxx{
            border-spacing: 0;/*把单元格间隙设置为0*/
            border-collapse: collapse;
        }
        .ddxx td{
            border: 1px solid black;
        }


        .ddmx{
            border-spacing: 0;/*把单元格间隙设置为0*/
            border-collapse: collapse;
        }

        .ddmx tr td{
            border : 1px solid black;
            width: 120px;
        }
        .ddmxt{
            border-spacing: 0;/*把单元格间隙设置为0*/
            border-collapse: collapse;
        }

        .ddmxt tr td{
            border: 1px solid black;
            width: 120px;
        }



        .ddmx2{
            border-spacing: 0;/*把单元格间隙设置为0*/
            border-collapse: collapse;
        }

        .ddmx2 tr td{
            border: 1px solid black;
            width: 240px;
        }
        .ddmxt2{
            border-spacing: 0;/*把单元格间隙设置为0*/
            border-collapse: collapse;/*设置单元格的边框合并为1*/
        }
        .ddmxt2 tr td{
            border: 1px solid black;
            width: 240px;
        }

    </style>
</head>
<body style="padding:0px;">
    
    <table id="dg" title="订单管理" class="easyui-datagrid" style="width:100%;height:auto"
            url="dingdanget"
            toolbar="#toolbar"
            rownumbers="true" fitColumns="true" fit="true" singleSelect="true" pagination="true">
        <thead>
            <tr>
                <th field="id" width="50" hidden="true">Id</th>
                <th field="username" width="50">用户名</th>
                <th field="user_id" width="50" hidden="true">用户id</th>
                <th field="opid" width="50">微信openid</th>
                <th field="tel" width="50">用户电话</th>
                <th field="money1" width="50">用户自定金额</th>
                <th field="money2" width="50">实际订单金额</th>
                <th field="times" width="50">下单时间</th>
                <th field="zt" width="50">订单状态</th>
                <th field="qhm" width="50">取货码</th>
                <th field="zw_guige" width="50">规格</th>
                <th field="zw_houdu" width="50">厚度(cm)</th>
                <th field="zw_yanse" width="50">颜色</th>
                <th field="zw_danwei" width="50">主瓦单位</th>
                <th field="infor" width="50">查看详细</th>
                <th field="clicks" width="50">订单确认</th>
                <th field="jz" width="50">是否结账</th>
            </tr>
        </thead>
    </table>
    <div id="toolbar">
        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="tg()">修改</a> -->

        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">删除</a> -->
        <form onsubmit="return false;">
            订单状态&nbsp;
            <font color="black">全部</font><input type="radio" name="shaixuan" value="1" onclick="suoyin(null)" checked="true" />&nbsp;
            <font color="red">未确认</font><input type="radio" name="shaixuan" value="1" onclick="suoyin(1)" />&nbsp;
            <font color="yellow">备货中</font><input type="radio" name="shaixuan" value="2" onclick="suoyin(2)" />&nbsp;
            <font color="green">待取货</font><input type="radio" name="shaixuan" value="3" onclick="suoyin(3)" />&nbsp;
            <font color="blue">已取货</font><input type="radio" name="shaixuan" value="4" onclick="suoyin(4)" />
        </form>

        <form onsubmit="return false;">
            结账状态&nbsp;
            <font color="black">全部</font><input type="radio" name="jzzt" value="0" onclick="jzztfun(null)" checked="true">&nbsp;
            <font color="black">未结账</font><input type="radio" name="jzzt" value="0" onclick="jzztfun(1)">&nbsp;
            <font color="black">已结账</font><input type="radio" name="jzzt" value="0" onclick="jzztfun(2)">
        </form>
    
        <form id="chaxunform" onsubmit="return false;">
            订单查询(查完记得选中“关闭”)&nbsp;
            <font color="red">启用</font><input type="radio" name="kongzhi" value="1" onclick="suoyin2(1)" />&nbsp;
            <font color="black">关闭</font><input type="radio" name="kongzhi" value="2" onclick="suoyin2(0)" checked="true"/>&nbsp;
            查询内容<input type="text" name="neirong" />&nbsp;&nbsp;
            <font color="black">空</font><input type="radio" name="shaixuan2" value="0" checked="true"  />&nbsp;
            <font color="black">姓名</font><input type="radio" name="shaixuan2" value="1"  />&nbsp;
            <font color="black">电话</font><input type="radio" name="shaixuan2" value="2"  />&nbsp;
            <font color="black">取货码</font><input type="radio" name="shaixuan2" value="3" />&nbsp;
            <font color="black">微信openid</font><input type="radio" name="shaixuan2" value="4" />&nbsp;
            <font color="black">日期</font><input id="dd1" type="text" class="easyui-datebox" required="required" name="time1">到
            <input id="dd2" type="text" class="easyui-datebox" required="required" name="time2">
            <button onclick="chaxun(this)">查询</button>
            <font color="red">注：日期为 第一个日期的00:00:00到第二个日期的23:59:59</font>
        </form>
        
        <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="tj()">查看筛选内容的统计信息</a>

        <!-- <a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">删除</a> -->
        
    </div>

    <div id="win">
        <p id="win_p_1">订单信息</p>
        <table class="ddxx" id="win_table_1">
            <tr>
                <td>姓名</td><td id="win_td_names"></td>
                <td>电话</td><td id="win_td_tel"></td>
                <td>下单时间</td><td id="win_td_times"></td>
            </tr>
            </tr>
                <td>状态</td><td id="win_td_zt"></td>
                <td>取货码</td><td id="win_td_qhm"></td>
                <td>订单金额</td><td id="win_td_money2"></td>
            </tr>
            </tr>
                <td>规格</td><td id="win_td_guige"></td>
                <td>厚度</td><td id="win_td_houdu"></td>
                <td>主瓦单位</td><td id="win_td_danwei"></td>
            </tr>
            <tr>
                <td>备注</td><td colspan="5" id="win_td_beizhu"></td>
            </tr>
        </table>

        <p id="win_p_2">订单明细</p>
        <table class="ddmx">
            <tr>
                <td>产品名称</td>
                <td>规格</td>
                <td>单位</td>
                <td>数量</td>
                <td>单价</td>
                <td>金额</td>
            </tr>
        </table>
        
        <table id="win_table_2" class="ddmxt"></table>

        <p id="win_p_3">主瓦明细</p>
        <table class="ddmx2">
            <tr>
                <td>长度</td>
                <td>块数</td>
                <td>总量(单位：<span id="zw_danwei_th"></span>)</td>
            </tr>
        </table>
        
        <table id="win_table_3" class="ddmxt2"></table>

    </div>



    <div id="win2"><!--统计信息查看-->
        输入管理员密码确认结账<input id="glymm" type="password" />
        <button id = "qrjz">确定</button>
    </div>

    <div id="win3"><!--统计信息查看-->
        <p>当前筛选条件下的统计结果如下</p>
        <p>订单数：<span id="win3_dds"></span></p>
        <p>金额总计：<span id="win3_jezj"></span></p>
        <p>其中已结账订单 <span id="win3_yjz_numb" style="color: green;"></span>个 金额：<span id="win3_yjz_money" style="color: green;"></span></p>
        <p>其中未结账订单 <span id="win3_wjz_numb" style="color: red;"></span>个 金额：<span id="win3_wjz_money" style="color: red;"></span></p>
        <button id="win3_pljz" onclick="dd_pljz()">批量结账</button>
    </div>

    <script>

    //全局筛选状态控制变量
    data = {
        username: null,
        tel:null,
        qhm:null,
        zt:null,
        opid:null,
        time1:null,
        time2:null,
        jz:null
    };

    (function(){//初始化详细信息面板
        $('#win').window({
            width:600,
            height:400,
            modal:true,
            title:"订单详情"
            });
        $('#win').window('close');
    })();
    (function(){//初始化确认结账面板
        $('#win2').window({
            width:600,
            height:100,
            modal:true,
            title:"确认结账"
            });
        $('#win2').window('close');
    })();
    (function(){//初始化订单总计面板
        $('#win3').window({
            width:600,
            height:200,
            modal:true,
            title:"订单统计"
            });
        $('#win3').window('close');
    })();
    
    function chakanxiangqing(did){//查看订单详情，生成一个浮动面板
        // alert(1);
        $.post('dingdan_xq',{id:did},function(result){
            // alert(result);
            // var res = eval('('+result+')');
            var res = result;
            console.log(res);
            if(res.code != 1){
                $.messager.show({	// show error message
                    title: 'Error',
                    msg: res.msg,
                    showSpeed:2000
                });
            }else{
                var dingdan = res.data.dingdan;
                var mingxi = res.data.mingxi;
                var zhuwa = res.data.zhuwa;
                console.log(dingdan);
                
                //面板数据更新
                //订单信息
                $("#win_td_names").html(dingdan.names);
                $("#win_td_tel").html(dingdan.tel);
                $("#win_td_times").html(dingdan.times) ;
                $("#win_td_zt").html(dingdan.zt);
                $("#win_td_qhm").html(dingdan.qhm);
                $("#win_td_money2").html(dingdan.money2);
                $("#win_td_beizhu").html(dingdan.beizhu);
                $("#win_td_guige").html(dingdan.zw_guige);
                $("#win_td_houdu").html(dingdan.zw_houdu);
                ;
                if(dingdan.zw_danwei == 1){
                    $("#win_td_danwei").html("米")
                    $("#zw_danwei_th").html("米");
                }else{
                    $("#zw_danwei_th").html("平方米");
                    $("#win_td_danwei").html("平方米")
                }
                
                
                //订单明细
                var mingxitxt = "";
                for(var i=0;i<mingxi.length;i++){
                    mingxitxt += "<tr>";
                    mingxitxt += "<td>"+mingxi[i].pro_names+"</td>";
                    mingxitxt += "<td>"+mingxi[i].guige+"</td>";
                    mingxitxt += "<td>"+mingxi[i].danwei+"</td>";
                    mingxitxt += "<td>"+mingxi[i].numbers+"</td>";
                    mingxitxt += "<td>"+mingxi[i].money2+"</td>";
                    mingxitxt += "<td>"+Math.ceil(mingxi[i].money2*mingxi[i].numbers*100)/100+"</td>";
                    mingxitxt += "</tr>";
                }
                //主瓦明细
                var zhuwatxt = "";
                for(var i=0;i<zhuwa.length;i++){
                    zhuwatxt += "<tr>";
                    zhuwatxt += "<td>"+zhuwa[i].changdu+"</td>";
                    zhuwatxt += "<td>"+zhuwa[i].numbers+"</td>";
                    if(dingdan.zw_danwei == 1){
                        zhuwatxt += "<td>"+zhuwa[i].chicun1+"</td>";
                    }else{
                        zhuwatxt += "<td>"+zhuwa[i].chicun2+"</td>";
                    }
                    
                    zhuwatxt += "</tr>";
                }

                $("#win_table_2").html(mingxitxt);
                $("#win_table_3").html(zhuwatxt);


                $('#win').window('open');
            }
        });
    }

    function dd_xiugai(did){//查看修改明细
        $.post('dd_xiugai_jl',{id:did},function(res){
            if(res.code!=1){
                $.messager.show({	// show error message
                    title: 'Error',
                    msg: res.msg,
                    showSpeed:2000
                });
            }else{
                var data = res.data;
                



            }
        })
    }

    function dd_jieshou(did){
        //接受订单
        $.post('dingdan_jieshou',{id:did},function(res){
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
    function dd_kequhuo(did){//状态变更为可取货
        $.post('dingdan_kequhuo',{id:did},function(res){
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
    function dd_quhuo(did){//订单变为已取货
        $.messager.prompt('提货确认', '请输入取货码进行确认', function(r){
            if (r){
                $.post('dingdan_quhuo',{id:did,qhm:r},function(res){
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
    //创建动态密码窗口
    function wins(func){
        // alert(111);
        $("#win2").window('open');
        $('#qrjz').unbind();
        $('#qrjz').bind('click',function(){
            $txt = $("#glymm").val();
            func($txt);
            $("#win2").window('close');
        });
    }
    function dd_jiezhang(did){//结账，吊起一个确认窗口
        wins(function(r){
            if (r){
                $.post('dingdan_jiezhang',{id:did,passwords:md5(r)},function(res){
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
    function suoyin(zts){//订单状态索引
        data.zt = zts;
        $('#dg').datagrid({
            queryParams: data
        });
        $('#dg').datagrid('reload');
    }

    function suoyin2(k){//关闭查询，将相关查询参数设为null
        if(k==0){
            data.username = null;
            data.tel = null;
            data.qhm = null;
            data.opid = null;
            data.time1 = null;
            data.time2 = null;

            $('#dg').datagrid({
                queryParams: data
            });
            $('#dg').datagrid('reload');
        }
    }

    function jzztfun(jz0){//结账状态索引
        data.jz = jz0;
        $('#dg').datagrid({
            queryParams: data
        });
        $('#dg').datagrid('reload');
    }
    function chaxun(obj){//点击查询按钮触发，将输入内容变更为表格刷新参数，刷新表格
        // alert(1);
        var fm = document.getElementById("chaxunform");
        var opens = fm.kongzhi.value;
        if(opens==2){
            //关闭
            alert("查询未启用");
            return;
        }

        //查询项确认
        var nr = fm.neirong.value;
        var sx = fm.shaixuan2.value;
        data.username = null;
        data.tel = null;
        data.qhm = null;
        data.opid = null;
        data.time1 = null;
        data.time2 = null;
        // alert(sx);
        // alert(nr);
        if(sx == 1){
            data.username = nr;
        }else if(sx == 2){
            data.tel = nr;
        }else if(sx == 3){
            data.qhm = nr;
        }else if(sx == 4){
            data.opid = nr;
        }

        //查询日期确认
        var time1 = fm.time1.value;
        var time2 = fm.time2.value;
        // alert(time1);
        // var time1 = $("#dd1").
        if(time1=="" && time2!="" || time2=="" && time1!=""){
            alert("日期必须都填或者都不填");
            return;
        }else if(time1!=null && time2!=null){
            data.time1 = time1;
            data.time2 = time2;
        }



        console.log(data);
        $('#dg').datagrid({
            queryParams: data
        });
        $('#dg').datagrid('reload');
    }



    function tj(){//筛选项的统计信息，将筛选条件发回服务器，返回一个服务器统计后的json数据，并展示在一个新窗口中
        $.post('dingdan_tongji',data,function(res){
            console.log(res);
            if(res.code != 1){
                $.messager.show({	// show error message
                    title: 'Error',
                    msg: res.msg,
                    showSpeed:2000
                });
            }else{
                $("#win3_dds").html(res.data.numbers);
                $("#win3_jezj").html(res.data.money2);
                $("#win3_wjz_money").html(res.data.djz);
                $("#win3_wjz_numb").html(res.data.djz_numb);
                $("#win3_yjz_money").html(res.data.yjz);
                $("#win3_yjz_numb").html(res.data.yjz_numb);
                $("#win3").window('open');
            }
        });
    }
    function dd_pljz(){//统计面板卡 批量结账按钮
        wins(function(r){
            // alert(1);
            if (r){
                var newdata = data;
                newdata['passwords'] = md5(r);
                console.log(newdata);
                $.post('dingdan_jiezhang_pl',newdata,function(res){
                    console.log(res);
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
            return;
        });
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