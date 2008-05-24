<div id="sidebar">
	<?php Plugins::act( 'theme_sidebar_top' ); ?>
	<div class="block" id="search">
		<?php include 'searchform.php'; ?>
	</div>
	<div class="block" id="desc">
		<p><?php Options::out('about'); ?></p>
	</div>
	<div class="block" id="menu">
		<h3>Pages</h3>
		<ul>
			<li><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>">Home</a></li>
			<?php
			foreach ( $pages as $tab ) {
			?>
			    <li><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
			<?php
			}
			if ( $user ) { ?>
			    <li><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Admin</a></li>
			<?php } ?>
		</ul>
	</div>
	<?php $theme->switcher(); ?>
	<div class="block" id="recent_comments">
		<h3>Recent comments</h3>
		<ul>
			<?php foreach($recent_comments as $recent_comment): ?>
			<li><span class="user"><a href="<?php echo $recent_comment->url; ?>"><?php echo $recent_comment->name; ?></a></span> on <a href="<?php echo $recent_comment->post->permalink; ?>"><?php echo $recent_comment->post->title; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php $theme->twitter (); ?>
	<?php $theme->show_blogroll(); ?> 
	<div class="block" id="recent_posts">
		<h3>Recent posts</h3>
		<ul>
			<?php foreach($recent_posts as $recent_post): ?>
			<li><a href="<?php echo $recent_post->permalink; ?>"><?php echo $recent_post->title; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="block" id="login">
		<h3>User</h3>
		<?php include 'loginform.php'; ?>
	</div>
	<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
</div>
<div id="content">
	<h1 id="blog_title"><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>