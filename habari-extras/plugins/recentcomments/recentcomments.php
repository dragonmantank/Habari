<!-- To customize this template, copy it to your currently active theme directory and edit it -->
<div id="recentcomments">
	<h2><?php echo $recentcomments_title; ?></h2>
	<ul>
	<?php foreach ( $recentcomments_links as $link ) : ?>
		<?php echo $link; ?>
	<?php endforeach; ?>
	</ul>
</div>