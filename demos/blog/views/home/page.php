
<div id="single" class="left">
			<?php foreach ($posts as $post):?>
	<div class="post" id="post-6628">
		<div class="entry-head clear pr">
			<div class="post-cmt block pa right"></div>
			<h2 class="left">
				<a
					href="<?php echo $this['baseUrl'];?>/post/<?php echo $post['id'];?>"><?php echo $post['title'];?></a>
			</h2>
		</div>
		<div class="entry clear">
			<span class="post-meta"><?php echo $post['addtime'];?></span>
			<p>
				<?php echo $post['intro'];?>
			</p>
		</div>
	</div>
			<?php endforeach;?>

<div class="navigation">
		<div class="page_navi">
		<?php foreach ($pages as $pageno):?>
		<?php if ($pageno == $page):?>
			<span class="page-numbers current"><?php echo $pageno;?></span>
		<?php else:?>
			<a href="<?php echo $this['baseUrl']?>/page/<?php echo $pageno;?>" class="page-numbers"><?php echo $pageno;?></a>
	   <?php endif;?>
	   <?php endforeach;?>
		</div>
	</div>

</div>