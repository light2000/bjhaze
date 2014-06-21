<!DOCTYPE html>
<html>
<head>
<meta charset="gbk">
<title>Administrator Pannel</title>
<script type="text/javascript"
	src="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/jquery.min.js"></script>
<script type="text/javascript"
	src="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/jquery.easyui.min.js"></script>
<link id="easyuiTheme" rel="stylesheet"
	href="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/themes/gray/easyui.css"
	type="text/css"></link>
<link rel="stylesheet" type="text/css"
	href="<?php echo $this->baseUrl;?>/static/jquery-easyui-1.3.5/themes/icon.css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $this->baseUrl;?>/static/admin.css">
<script type="text/javascript">
		$(function(){
			$('.sider li a').click(function() {
				var classId = 'index';
				var subtitle = $(this).text();
				var url = $(this).attr('cmshref');

				if (!$('#tabs_' + classId).tabs('exists', subtitle)) {
					$('#tabs_' + classId).tabs('add', {
						title : subtitle,
						content : subtitle,
						closable : true,
						href : url,
						tools : []
					});
					return false;
				} else {
					$('#tabs_' + classId).tabs('select', subtitle);
					return false;
				}
			});
		});
	</script>
</head>
<body class="easyui-layout">
	<noscript>
		<div
			style="position: absolute; z-index: 100000; height: 246px; top: 0px; left: 0px; width: 100%; background: white; text-align: center;">
			no script, no administrator pannel..</div>
	</noscript>
	<div data-options="region:'north',border:false"
		style="height: 60px; background: #fff; padding: 0px">
		<div class="site_title">administrator pannel</div>
		<div id="sessionInfoDiv"
			style="position: absolute; right: 5px; top: 10px;">
			[<strong>welcome <?php echo $this->session->username;?></strong>], last login at <?php echo $this->session->last_login_time;?> with ip <strong><?php echo $this->session->last_login_ip;?></strong>
		</div>
		<div style="position: absolute; right: 5px; bottom: 5px;">
			<a href="logout">Logout</a>
		</div>
	</div>
	<div data-options="region:'west',split:true,title:'administrator menu'"
		style="width: 200px;">
		<div class="easyui-accordion sider"
			data-options="fit:true,border:false">

	<?php foreach ($menuList as $menu):?>
	<div title="<?php echo $menu['name'];?>"
				data-options="iconCls:'icon-mini-add'" style="padding: 10px;">
				<ul class="easyui-tree" data-options="animate:false">
			<?php foreach ($menu['children'] as $menu2):?>
			<?php if (!empty($menu2['children'])):?>
			<li data-options="state:'open'"><span><?php echo $menu2['name'];?></span>
						<ul>
						<?php foreach ($menu2['children'] as $menu3):?>
						<li><a href="javascript:void;"
								cmshref="<?php echo $menu3['target'];?>" type="nav_head" rel=""><?php echo $menu3['name'];?></a></li>
						<?php endforeach;?>
					</ul></li>
			<?php else:?>
			<li><a href="javascript:void;"
						cmshref="<?php echo $menu2['target'];?>" type="nav_head" rel=""><?php echo $menu2['name'];?></a></li>
			<?php endif;?>
			<?php endforeach;?>
		</ul>
			</div>
	<?php endforeach;?>
		</div>

	</div>
	<div data-options="region:'south',border:false"
		style="height: 50px; background: #fff; padding: 10px;">
		<div id="footer">Copyright &copy; 2014 by bjhaze. All Rights Reserved
		</div>
	</div>

	<div data-options="region:'center'" class="indexcenter">
		<div id="tabs_index" class="easyui-tabs" fit="true" border="false">
			<div style="font-size: 18px;text-align: center;margin-top: 200px" title="Welcome">
				Welcome <?php echo $this->session->username;?>, you have stay <?php echo ceil((time() - $this->session->login_time)/60);?> minutes on this blog.
			</div>
		</div>
	</div>

	<div id="dialog_cms" data-options="iconCls:'icon-save'"></div>
</body>
</html>
