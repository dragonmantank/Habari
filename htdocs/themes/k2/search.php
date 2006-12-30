<?php $theme->header(); ?>
<div class="content">
	<div id="primary">
	<div id="primarycontent" class="hfeed">
		<h2>Search results for <?php echo $_REQUEST['criteria']; ?></h2>
	<?php foreach ( $posts = Posts::search($_REQUEST['criteria']) as $post ) { ?>
		<div class="entry-head">
			<h3 id="entry-title" class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title; ?></a></h3>
			<small class="entry-meta">
				<span class="chronodata">
					<abbr class="published"><?php echo $post->pubdate; ?></abbr>
				</span>
				<span class="commentslink"><a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?> Comments</a></span>
				<span class="entry-tags"><?php echo Format::tag_and_list($post->tags); ?></span>
			</small>
		</div>
			<div class="entry-content">
				<?php echo $post->content; ?>
			</div>
		<?php } ?>
	</div>	
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
		