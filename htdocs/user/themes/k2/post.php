<?php include_once('header.php'); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
				<div id="post-<?php echo $post->id; ?>">
					<div class="entry-head">
						<h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->pubdate_out; ?></abbr>
							</span>
        <span class="commentslink"><?php echo $post->comments->approved->count; ?>
        <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count );
        ?></span>
                            <?php
                            if ( User::identify() ) { ?><span class="entry-edit"><a href="<?php
                                 URL::out('admin', 'page=publish&slug=' . $post->slug);
                                ?>" title="Edit post">Edit</a></span><?php
                            }
                            ?>
						</small>
							<div class="entry-content">
								<?php echo $post->content_out; ?>
							</div>
					</div>
				</div>
		<?php include_once( 'comments.php' ); ?>
		</div>
	</div>
	<hr />
	<div class="secondary">
		<div id="search"><h2>Search</h2>
			<form method="get" id="searchform" action="/index.php">
				<input type="text" id="s" name="s" value="search blog archives" />
				<input type="submit" id="searchsubmit" value="go" />
			</form>
		</div>	
		<div class="sb-about">
			<h2>About</h2>
			<p><?php Options::out('about'); ?></p>
		</div>	
	</div>
	<div class="clear"></div>
</div>
<?php include_once('footer.php'); ?>
