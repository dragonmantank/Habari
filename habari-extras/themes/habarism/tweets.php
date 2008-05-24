<div class="block" id="twitter">
	<h3>Twitter</h3>
	<ul>
		<li><em><?php echo htmlspecialchars( $tweet_text ); ?></em></li>
		<li>Via <a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter:username' )); ?>">Twitter</a></li>
	</ul>
</div>