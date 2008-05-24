<div class="block" id="blogroll">
	<h3><?php echo $blogroll_title; ?></h3>
	<ul>
	<?php if ( ! empty( $blogs ) ) { foreach( $blogs as $blog ) { ?>
		<li><a href="<?php echo $blog->url; ?>"><?php echo $blog->name; ?></a></li>
	<?php } } ?>
	</ul>
</div>