<div id="header" class="auto clear">
	<div class="wrapper">
		<div class="logo left" style="font-size: 32px; font-weight: bold;">
			<a href="<?php echo $this['baseUrl'];?>"><?php echo $this['site_name'];?></a>
		</div>
		<div id="header-nav" class="right">
			<ul class="nav left n320">
			<?php foreach ($categories as $category):?>
				<li><a href="/category/<?php echo $category['id'];?>" class="block"><?php echo $category['category_name'];?></a></li>
			<?php endforeach;?>
			</ul>
		</div>
	</div>
</div>