
<div class="sidebar-widget">
	<div class="sidebar-div block">
		<h4 class="sidebar-title">New Posts</h4>
	</div>
	<ul class="sidebar-list">
		<?php foreach ($posts as $post):?>
			<li><a
			href='<?php echo $this['baseUrl']?>/post/<?php echo $post['id'];?>'><?php echo $post['title'];?></a></li>
			<?php endforeach;?>
		</ul>
</div>