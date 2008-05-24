<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div id="content">
		<div class="post">
			<div class="post_head">
				<h2>Please log in</h2>
			</div>
			<div class="post_content">
				<?php include 'loginform.php'; ?>
				<?php Plugins::act( 'theme_login' ); ?>
			</div>
		</div>
	</div>
<?php include 'footer.php'; ?>
