<?php
$page= isset($index) ? $index : 1;
?>
<?php $theme->header(); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<h2>Search results for <?php echo $criteria; ?></h2>
			<?php foreach ( $posts = Posts::search($criteria, $page) as $post ) { ?>
			<div class="entry-head">
				<h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->out_title; ?></a></h3>
				<small class="entry-meta">
					<span class="chronodata">
						<abbr class="published"><?php echo $post->out_pubdate; ?></abbr>
					</span>
					<span class="commentslink"><a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> Comments</a></span>
					<span class="entry-tags"><?php echo $post->out_tags; ?></span>
				</small>
			</div>
			<div class="entry-content">
				<?php echo $post->out_content; ?>
			</div>
			<?php } ?>
		</div>
		<div id="page-selector"><strong>Page:</strong><?php echo Utils::page_selector($page, Utils::archive_pages(Posts::count_last()), 'search', array('criteria'=>$criteria) ); ?></div>
	</div>	
	<hr />
	<div class="secondary">
		<div id="search"><h2>Search</h2>
			<form id="searchform" action="<?php URL::out('search'); ?>">
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
