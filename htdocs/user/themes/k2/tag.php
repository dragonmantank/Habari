<?php
$display= array(
	'status' => Post::status('published'), 
	'tag' => $tag,
	'content_type' => Post::type('entry'),
	'page' => isset(index) ? $index : 1,
);
?>
<?php $theme->header(); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php foreach ( $posts = Posts::get($display) as $post ) { ?>
				<div id="post-<?php echo $post->id; ?>">
					<div class="entry-head">
						<h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->pubdate_out; ?></abbr>
							</span>
							<span class="commentslink"><a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> Comments</a></span>
							<span class="entry-tags"><?php echo $post->tags_out; ?></span>
						</small>
					</div>
					<div class="entry-content">
						<?php echo $post->content_out; ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<div id="page-selector"><strong>Page:</strong><?php echo Utils::page_selector($display['page'], Utils::archive_pages(Posts::count_last()), 'tag', array( 'tag'=>$display['tag'] ) ); ?></div>
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
