<?php include_once('header.php'); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
				<div id="error">
					<div class="entry-head">
						<h3 class="entry-title">Error!</h3>
							<div class="entry-content">
								The requested post was not found.
							</div>
					</div>
				</div>
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
