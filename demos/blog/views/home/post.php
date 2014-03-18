<div id="single" class="left">
	<div id="post-6634" class="post">
		<div class="entry-head clear pr">
			<div class="post-cmt block pa right">
				<a><?php echo count($comments);?></a>
			</div>
			<h2 class="left">
				<a
					href="<?php echo $this['baseUrl'];?>/post/<?php echo $post['id'];?>"><?php echo $post['title'];?></a>
			</h2>
		</div>
		<div class="entry clear">
			<span class="post-meta"><?php echo $post['addtime'];?></span> <span
				class="post-love"></span>
			<p><?php echo $post['content'];?></p>
		</div>
	</div>
	
	<div id="comments">
		<div class="ds-thread" id="ds-thread">
			<div id="ds-reset">
				<div class="ds-comments-info">
					<ul class="ds-comments-tabs">
						<li class="ds-tab"><a href=""
							class="ds-comments-tab-duoshuo ds-current"><span
								class="ds-highlight ds-comments-tab-duoshuo ds-current"><?php echo count($comments);?></span>
								comments</a></li>
					</ul>
				</div><a name="comments"></a>
				<ul class="ds-comments">
<?php foreach ($comments as $comment):?>
					<li class="ds-post">
						<div class="ds-post-self">
							<div class="ds-avatar">
								<img alt=""
									src="<?php echo $this['baseUrl'];?>/static/images/head.jpg">
							</div>
							<div class="ds-comment-body">
								<div class="ds-comment-header">
									<p target="_blank" rel="nofollow"
										class="ds-user-name ds-highlight"><?php echo $comment['user_ip'];?></p>
								</div>
								<p><?php echo $comment['content'];?></p>

							</div>
						</div>
					</li>
<?php endforeach;?>
				</ul>

				<div class="ds-replybox">
					<a href="javascript:void(0);"
						class="ds-avatar"><img alt=""
						src="<?php echo $this['baseUrl'];?>/static/images/head.jpg"></a>
					<form method="post" action="<?php echo $this['baseUrl'];?>/comment">
						<input type="hidden" value="<?php echo $post['id'];?>" name="comment[blog_id]">
						<div class="ds-textarea-wrapper ds-rounded-top">
							<textarea name="comment[content]"></textarea>
							<pre class="ds-hidden-text"></pre>
						</div>
						<div class="ds-post-toolbar">
							<div class="ds-post-options ds-gradient-bg">
								<span class="ds-sync"></span>
							</div>
							<button type="submit" class="ds-post-button">Submit</button>
							<div class="ds-toolbar-buttons">
								<a class="ds-toolbar-button ds-add-emote"></a><a
									class="ds-toolbar-button ds-add-image"></a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

</div>
