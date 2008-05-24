<!-- Creditsdue:  To modify this template, copy it from the plugin directory to your current theme directory. -->
<div class="credits">
	<h3>Theme</h3>
		<ul id="theme_credits">
			<li><?php echo $theme_credits->name. " version " . $theme_credits->version . " by <a href='" . $theme_credits->url . "'>" . $theme_credits->author . "</a>" ?></li>
		</ul>

	<h3>Plugins</h3>
		<ul id="plugin_credits">
		
 			<?php foreach ($plugin_credits as $plugin) { ?>
			<li><?php echo "<a href='" . $plugin->info->url . "'>" . $plugin->info->name . "</a> version " . 	$plugin->info->version .  " by <a href='" . $plugin->info->authorurl . "'>" . $plugin->info->author . "</a>" ?></li>

<?php } ?>

		</ul>
</div>