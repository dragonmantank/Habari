<?php

define('HABARI_PATH', dirname(__FILE__));
include_once( 'system/init.php' );

?>
<?php include_once('themes/k2/header.php'); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php $posts = posts::retrieve(); ?>
			<?php foreach ( $posts as $post ) { ?>
				<div id="<?php echo $post->guid; ?>">
					<div class="entry-head">
						<h3 class="entry-title"><a href="/<?php echo $post->slug; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->pubdate; ?></abbr>
							</span>
							<span class="commentslink">Closed</span>
						</small>
							<div id="desc" class="entry-content">
							<?php echo $post->content; ?>
							</div>
					</div>
				</div>
			<?php } ?>
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
				<p>This is a test install of Habari.</p>
	</div>	
</div>
<div class="clear"></div>
</div>
<?php include_once('themes/k2/footer.php'); ?>