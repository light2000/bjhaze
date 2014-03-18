
<div class="sidebar-widget">
	<div class="sidebar-div block">
		<h4 class="sidebar-title">Comments</h4>
	</div>
	<ul class="sidebar-list">
	<?php foreach ($comments as $key => $comment):?>
		<li><a href="<?php echo $this['baseUrl']?>/post/<?php echo $comment['blog_id'];?>#comments" target="_blank"><?php echo $comment['content'];?></a></li>
	<?php endforeach;?>
	</ul>
</div>