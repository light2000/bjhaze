<div id="footer" class="auto clear">
	<div class="legal wrapper">
		<div class="left">
			<p>
			<?php foreach ($categories as $category):?>
				<a href="<?php echo $this['baseUrl'];?>/category/<?php echo $category['id'];?>"><?php echo $category['category_name'];?></a><span
					class="sep">|</span>
			<?php endforeach;?>
			</p>
			<p>&copy; 2014 bjhaze</p>
		</div>
		<div class="right">
			<p>
				<a href="<?php echo $this['baseUrl'];?>/admin/home/login">administrator login</a>
			</p>
		</div>
	</div>
</div>