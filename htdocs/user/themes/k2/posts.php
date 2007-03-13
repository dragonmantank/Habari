<?php include 'header.php'; ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php foreach ( $posts as $post ) { ?>
				<div id="post-<?php echo $post->id; ?>">
					<div class="entry-head">
						<h3 class="entry-title">
			              <a href="<?php echo $post->permalink; ?>" rel="bookmark" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a>
			            </h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->pubdate_out; ?></abbr>
							</span>
							<span class="commentslink">
				                <a href="<?php echo $post->permalink; ?>" title="Comments on this post">
				                  <?php echo $post->comments->approved->count; ?> Comments
				                </a>
              				</span>
                            <?php
                            if ( User::identify() ) { ?><span class="entry-edit"><a href="<?php
                                 URL::out('admin', 'page=publish&slug=' . $post->slug);
                                ?>" title="Edit post">Edit</a></span><?php
                            }
                            ?>
						<span class="entry-tags"><?php echo $post->tags_out; ?></span>
						</small>
						<div class="entry-content">
							<?php echo $post->content_out; ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<div id="page-selector"><strong>Page:</strong>
      		<?php echo Utils::page_selector(isset($page) ? $page : 1, Utils::archive_pages(Posts::count_last()), 'display_posts_at_page' ); ?>
    	</div>
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
			<?php include 'loginform.php'; ?>
		</div>	
	</div>
	<div class="clear"></div>
</div>
<?php include 'footer.php';?>
