
<div class="sidebar-widget">
	<div class="sidebar-div block">
		<h4 class="sidebar-title">Archives</h4>
	</div>
	<ul class="sidebar-list">
	<?php foreach ($archives as $key => $link):?>
		<li><a href="<?php echo $link;?>" target="_blank"><?php echo $key;?></a></li>
	<?php endforeach;?>
	</ul>
</div>