<!doctype html>
<html>
<head>
<title><?php echo $this['site_name'];?></title>
<link rel="stylesheet" type="text/css" media="all"
	href="<?php echo $this['baseUrl'];?>/static/style.css" />
<meta charset="gbk" />
<meta name="keywords" content="<?php echo $this['site_keywords'];?>" />
<meta name="description"
	content="<?php echo $this['site_description'];?>" />
</head>
<body>
	<div id="top" class="bing-bg"></div>
<?php $this->widget('header');?>
<div id="content" class="auto clear">
		<div class="wrapper">
        <?php include $mainFile;?>
        <div id="sidebar" class="right">
            <?php $this->widget('newposts');?>
            <?php $this->widget('comments');?>
            <?php $this->widget('links')?>
		    <div class="sidebar-widget">
					<div class="sidebar-div block">
						<a href="#top" class="btn-scroll"><h4 class="sidebar-title">
								<strong>&#8743;</strong>return top
							</h4></a>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
<?php $this->widget('footer');?>
</body>
</html>