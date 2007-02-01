<?php include_once('header.php'); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php include_once('loginform.php');?>
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
			<?php if ( User::identify() ) { ?>
			<h2>User</h2>
			<p><?php echo User::identify()->username; ?></p>
			<?php } ?>
		</div>	
	</div>
	<div class="clear"></div>
</div>
<?php include_once('footer.php'); ?>
