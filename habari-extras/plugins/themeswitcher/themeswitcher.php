<?php
$active_theme= Options::get( 'theme_dir' );
$all_themes= Themes::get_all_data();
$selected_themes= Options::get( 'themeswitcher:selected_themes' );
?>
	<div class="sb-switcher">
		<h2>Theme Switcher</h2>
		<form action="" method="post" name="themeswitcher">
			<select name="theme_dir">
				<?php
				foreach( $selected_themes as $selected_theme ) {
					$theme= $all_themes[$selected_theme];
				?>
					<option value="<?php echo $theme['dir']; ?>"<?php echo ($theme['dir'] == $active_theme) ? 'selected' : ''; ?>><?php echo $theme['info']->name; ?> <?php echo $theme['info']->version; ?></option>
				<?php } ?>
			</select>
			<input type="submit" name="themeswitcher_submit" value="Switch">
		</form>
	</div>