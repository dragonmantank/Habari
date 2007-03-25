<?php include('header.php'); ?>
<div id="content-area">
	<div class="dashboard-block c3" id="welcome">
		<h1>Import</h1>
		<form method="post">
			<?php
			if(empty($_POST['importer'])) :
				$import_names= array();
				$import_names= Plugins::filter('import_names', $import_names);
			?>
			<p>Please choose the type of import to perform:</p>
			<select name="importer">
				<option></option>
				<?php
				foreach($import_names as $name) {
					echo "<option>{$name}</option>";
				}
				?>
			</select>
			<p class="submit"><input type="submit" name="import" value="Select" /></p>
			<?php
			else:
				echo Plugins::filter('import_stage', '', @$_POST['importer'], @$_POST['stage'], @$_POST['step']);
			?>
			<?php
			endif;
			if(isset($_POST['importer'])) {
				echo '<input type="hidden" name="importer" value="' . $_POST['importer'] . '">';
			}
			?>
		</form>

	</div>
</div>
<?php
// unset the $db_connection variable, since we don't need it any more
unset( $db_connection );
?>
<?php include('footer.php'); ?>