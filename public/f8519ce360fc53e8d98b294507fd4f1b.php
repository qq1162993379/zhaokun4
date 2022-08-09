<?php /*a:1:{s:30:"../app/view/qiantai/admin.html";i:1657775214;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset=utf-8>
	<title>前台管理</title>
	<link rel="stylesheet" type="text/css" href="/static/css/easyui/easyui.css">
    <link rel="stylesheet" type="text/css" href="/static/css/easyui/icon.css">
    <!-- <link rel="stylesheet" type="text/css" href="__CSS__/myStyle.css"> -->
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="/static/js/easyui-lang-zh_CN.js"></script>
</head>
<body>  
<div class="easyui-layout" fit="true">
	
	<div data-options="region:'west',split:true" title="功能菜单" style="width:200px;">
		<div class="easyui-accordion" data-options="multiple:true">	
			<div title="前台" data-options="iconCls:'icon-edit'" style="overflow:auto;padding:10px;">
		        <div style="margin-bottom:5px">
					<a href="#" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-picture_edit'" style="width:100%" onclick="addTab('订单管理','dingdan_new_list')">订单管理</a>
					<!-- <a href="#" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-picture_edit'" style="width:100%" onclick="addTab('留言管理','/qiantai/homepage')">留言管理</a>
					<a href="#" class="easyui-linkbutton" data-options="plain:true,iconCls:'icon-picture_edit'" style="width:100%" onclick="addTab('提问管理','/qiantai/homepage')">提问管理</a> -->

				</div>
			</div>
		</div>
    </div>
	<div data-options="region:'center',title:'订单管理系统'">
		<div id="tt" class="easyui-tabs" style="width:100%;height:100%;">
		</div>	
	</div>
</div>
<script>
function addTab(title, url){
	if ($('#tt').tabs('exists', title)){
		$('#tt').tabs('select', title);
	} else {
		var content = '<iframe scrolling="auto" frameborder="0"  src="'+url+'" style="width:100%;height:99%;"></iframe>';
		$('#tt').tabs('add',{
			title:title,
			content:content,
			closable:true
		});
	}
}
</script>
    
</body>
</html>