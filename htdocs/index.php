<?php

define('HABARI_PATH', dirname(__FILE__));

/**
 * function __autoload
 * Autoloads class files for undeclared classes.
 **/  
function __autoload($class_name) {
	if(file_exists(HABARI_PATH . '/user/classes/' . strtolower($class_name) . '.php'))
		require_once HABARI_PATH . '/user/classes/' . strtolower($class_name) . '.php';
	else if(file_exists(HABARI_PATH . '/system/classes/' . strtolower($class_name) . '.php'))
		require_once HABARI_PATH . '/system/classes/' . strtolower($class_name) . '.php';
	else
		die( 'Could not include class file ' . strtolower($class_name) . '.php' );
}

// Load the config
if(file_exists(HABARI_PATH . '/config.php')) {
	require_once HABARI_PATH . '/config.php';
} else {
	die('There are no database connection details.  Please rename config-sample.php to config.php and edit the settings therein.');	
}

// Connect to the database or fail informatively
try {
	$db = new habari_db( $db_connection['connection_string'], $db_connection['username'], $db_connection['password'] );
}
catch( Exception $e) {
	die( 'Could not connect to database using the supplied credentials.  Please check config.php for the correct values. Further information follows: ' .  $e->getMessage() );		
}
unset($db_connection);

// Create the global options instance
$options = new Options();

// Install the database tables if they're not already installed
Installer::install();


?>
<?php include_once(HABARI_PATH . '/themes/k2/header.php'); ?>
<div class="content">
	<div id="primary">
		<div id="primarycontent" class="hfeed">
			<?php foreach ( Post::get_posts() as $post ) { ?>
				<div id="<?php echo $post->guid; ?>">
					<div class="entry-head">
						<h3 id="entry-title" class="entry-title"><a href="/<?php echo $post->slug; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title; ?></a></h3>
						<small class="entry-meta">
							<span class="chronodata">
								<abbr class="published"><?php echo $post->pubdate; ?></abbr>
							</span>
							<span class="commentslink">Closed</span>
						</small>
							<div id="entry-content" class="entry-content">
								<?php echo $post->content; ?>
							</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
	<hr />
<div class="secondary">
	<div id="search"><h2>Search</h2>
<form method="get" id="searchform" action="/index.php">
	<input type="text" id="s" name="s" value="search blog archives" />
	<input type="submit" id="searchsubmit" value="go" />
</form>
	</div>	
	<div class="sb-about">
		<h2>About</h2>
				<p><?php echo $options->about; ?></p>
	</div>	
</div>
<div class="clear"></div>
</div>
<?php include_once('themes/k2/footer.php'); ?>
