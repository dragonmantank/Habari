	</div>
	<div id="footer">
		<?php Options::out('title'); _e(' is powered by'); ?> <a href="http://www.habariproject.org/" title="Habari">Habari</a> and <a rel="nofollow" href="http://blog.theundersigned.net/">Georgia</a><br>
		<a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Atom Entries</a> and <a href="<?php URL::out( 'atom_feed_comments' ); ?>">Atom Comments</a>
		<?php $theme->footer(); ?>
	</div>
</div>
<?php
// Uncomment this to view your DB profiling info
// include 'db_profiling.php';
?>
</body>
</html>