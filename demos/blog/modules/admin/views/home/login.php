<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk">
<title>Administrator Login</title>
<link rel="stylesheet" type="text/css"
	href="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/themes/default/easyui.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/themes/icon.css">
<script type="text/javascript"
	src="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/jquery.min.js"></script>
<script type="text/javascript"
	src="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/jquery.easyui.min.js"></script>
<script type="text/javascript">
		$(function(){
			$('#win').window({
				collapsible:false,
				minimizable:false,
				maximizable:false,
				resizable:false,
				closable:false,
				draggable:false
			});
			$('#login_form').form({
				url:'<?php echo $this->baseUrl;?>/admin/home/login',
                success:function(data){
                    var data = eval('(' + data + ')');
                    if (!data.success)
                        $.messager.alert('Sorry', data.message, 'error');
                    else
                        document.location.href = '<?php echo $this->baseUrl;?>/admin/home/';
                }
            });
		});
</script>
<style>
#login_form em {
	float: left;
	width: 56px;
	line-height: 22px;
	font-style: normal;
}
</style>
</head>
<body>
	<div id="win" class="easyui-window" title="login pannel"
		style="width: 350px; height: 200px;">
		<form id="login_form" style="padding: 10px 20px 10px 40px;" method="post">
			<p>
				<em>username</em><input class="easyui-validatebox"
					data-options="required:true" type="text" value="admin" name="username">
			</p>
			<p>
				<em>password</em><input class="easyui-validatebox"
					data-options="required:true" type="password" value="admin" name="password">
			</p>
			<div style="padding: 5px; text-align: center;">
				<a href="#" onclick="$('#login_form').submit();"
					class="easyui-linkbutton" icon="icon-ok">submit</a> <a href="#"
					onclick="$('#login_form').form('clear');" class="easyui-linkbutton"
					icon="icon-reload">reset</a>
			</div>
		</form>
	</div>
</body>
</html>