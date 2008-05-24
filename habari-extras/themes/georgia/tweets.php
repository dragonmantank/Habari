<div class="block" id="twitter">
	<p class="tweet"><?php echo htmlspecialchars( $tweet_text ); ?></p>
	<p class="via">Via <a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter:username' )); ?>">Twitter</a></p>
</div>