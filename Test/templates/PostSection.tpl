{@PostSection}
<div class="article">
	<div class="title">
		<small>{$author} | {$date}</small>
		<h2><a href="{$url}" rel="bookmark">{$title}</a></h2>
	</div>
	<div class="post">
		<div class="entry">
			{$content}
		</div>
		<div class="postinfo">
			<div class="com"><a href="{$url}#respond" title="Comment on {$title}">Leave a Comment</a></div>
			<div>Posted in 
				<ul class="taglist">
					{?categories}
						<li><a href="{$url}" title="View all posts in {$name}" rel="category tag">{$name}</a></li>
					{/?}
				</ul>
			</div>
		</div>
	</div>
</div>
{/@}