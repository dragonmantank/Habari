<?php include 'header.php'; ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php include 'loginform.php'; ?>
		</div>
	</div>
	<hr>
	<div class="secondary">
		<div id="search">
			<h2>Search</h2>
			<?php include 'searchform.php'; ?>
		</div>	
		<div class="sb-about">
			<h2>About</h2>
			<p><?php Options::out( 'about' ); ?></p>
		</div>
		<div class="sb-user">
			<?php if ( $user ) { ?>
			<h2>User</h2>
			<p><?php echo $user->username; ?></p>
			<?php } ?>
		</div>	
	</div>
	<div class="clear"></div>
</div>
<?php include 'footer.php'; ?>
