<?php include_once('header.php'); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
				<div id="<?php echo $post->id; ?>">
					<div class="entry-head">
						<h3 id="entry-title" class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->out_title; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->out_pubdate; ?></abbr>
							</span>
							<span class="commentslink">Closed</span>
						</small>
							<div id="entry-content" class="entry-content">
								<?php echo $post->out_content; ?>
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
