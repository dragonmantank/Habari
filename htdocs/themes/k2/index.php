<?php
$display = array(
	'status'=>Post::STATUS_PUBLISHED,
	'content_type'=>Post::TYPE_ENTRY,
	'page'=> isset(URL::o()->settings['index']) ? URL::o()->settings['index'] : 1,
);
?>
<?php $theme->header(); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php foreach ( $posts = Posts::get($display) as $post ) { ?>
				<div id="<?php echo $post->guid; ?>">
					<div class="entry-head">
						<h3 id="entry-title" class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->out_title; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->out_pubdate; ?></abbr>
							</span>
							<span class="commentslink"><a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> <?php echo _n('Comment', 'Comments', $post->comments->approved->count); ?></a></span>
							<span class="entry-tags"><?php echo $post->out_tags; ?></span>
						</small>
							<div id="entry-content" class="entry-content">
								<?php echo $post->out_content; ?>
							</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<div id="page-selector"><strong>Page:</strong><?php echo Utils::page_selector(isset(URL::o()->settings['index']) ? URL::o()->settings['index'] : 1, Utils::archive_pages(Posts::count_last()), 'home' ); ?></div>
	</div>
	<hr />
<div class="secondary">
	<div id="search"><h2>Search</h2>
		<form id="search" action="<?php URL::out('search'); ?>">
			<input type="text" name="criteria" />
			<input id="searchsubmit" type="submit" name="search" value="Search" />
		</form>
	</div>	
	<div class="sb-about">
		<h2>About</h2>
				<p><?php Options::out('about'); ?></p>
		<h2>User</h2>
			<p><?php $theme->loginform(); ?></p>
	</div>	
</div>
<div class="clear"></div>
</div>
<?php $theme->footer(); ?>
