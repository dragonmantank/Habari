		<div id="sidebar">
			<div class="block">
				<form method="get" id="search" action="<?php URL::out('display_search'); ?>">
					<p><input type="text" id="searchfield" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>"> <input type="submit" id="searchsubmit" value="<?php _e('Search'); ?>"></p>
				</form>
			</div>
			<div class="block">
				<h2>Recent Comments</h2>
				<?php $theme->show_recentcomments(); ?>
			</div>
			<div class="block">
				<h2>Monthly Archives</h2>
				<?php $theme->monthly_archives(5, 'N'); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/archives">More...</a>
			</div>
			<div class="block">
				<h2>Tags</h2>
				<?php $theme->tag_cloud(5); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/tag">More...</a>
			</div>
		</div>
		<div id="footer">
			<p><?php echo $copyright; ?> Subscribe to <a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Entries</a> or <a href="<?php URL::out( 'atom_feed_comments', array( 'index' => '1' ) ); ?>">Comments</a></p>
		</div>

		<?php $theme->footer(); ?>

	</body>
</html>