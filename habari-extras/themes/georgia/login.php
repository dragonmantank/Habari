<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
	<div class="post">
		<h2 class="post_title">Please log in</h2>
		<div class="post_content">
			<?php include 'loginform.php'; ?>
			<?php Plugins::act( 'theme_login' ); ?>
		</div>
	</div>
<?php include 'footer.php'; ?>
