<!-- sidebar -->
		<?php Plugins::act( 'theme_sidebar_top' ); ?>
		<div class="block" id="search">
			<h3>Search</h3>
			<?php include 'searchform.php'; ?>
		</div>
		<div class="block" id="flickr">
			<div class="images clearfix">
			<script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=10&amp;display=latest&amp;size=s&amp;layout=x&amp;source=user&amp;user=35634769@N00"></script>
			</div>
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
		<div class="block" id="footer">
			<p><?php Options::out('title'); _e(' is powered by'); ?> <a href="http://www.habariproject.org/" title="Habari">Habari</a> and <a rel="nofollow" href="http://blog.theundersigned.net/">Habarism</a><br>
			<a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Atom Entries</a> and <a href="<?php URL::out( 'atom_feed_comments' ); ?>">Atom Comments</a></p>
			<?php $theme->footer(); ?>
		</div>
		<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
	</div>    
<!-- /sidebar -->