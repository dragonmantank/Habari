<?php

/**
 * FeedList Plugin - Makes feeds available for output in a <ul> from a theme.
 * 
 * To display the feed list in the template output, add this code where the 
 * list should be displayed:
 * <code>
 * <?php echo $feedlist; ? >
 * </code>
 */ 

class FeedList extends Plugin
{ 
	// Define the feed to load.  Will eventually be done via Plugins Admin interface.
	const FEEDLIST_FEED= 'http://del.icio.us/rss/ringmaster/';

	// Version info
	const VERSION= '0.1';
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'Feed List',
			'url' => 'http://asymptomatic.net/',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => self::VERSION,
			'description' => 'Outputs a feed as an unordered list.',
			'license' => 'ASL',
		);
	}
	
	/**
	 * Plugin init action, executed when plugins are initialized.
	 */ 
	public function action_init()
	{
		// Register the name of a new database table.
		DB::register_table('feedlist');
	}
	
	/**
	 * Plugin plugin_activation action, executed when any plugin is activated
	 * @param string $file The filename of the plugin that was activated.
	 */ 
	public function action_plugin_activation( $file )
	{
		// Was this plugin activated?
		if(realpath($file) == __FILE__) {
			// Register a default event log type for this plugin
			EventLog::register_type();  // Defaults to ( "default", __CLASS__ )
			// Register the name of a new database table
			DB::register_table('feedlist');  // 'plugin_activation' hook is called without first calling 'init'
			// Create the database table, or upgrade it
			DB::dbdelta( '
				CREATE TABLE ' . DB::table('feedlist') . ' (
					id INT NOT NULL AUTO_INCREMENT,
					feed_id INT NOT NULL DEFAULT 0,
					guid VARCHAR(255) NOT NULL,
					title VARCHAR(255) NOT NULL,
					link VARCHAR(255) NOT NULL,
					updated DATETIME NOT NULL,
					description TEXT,
					PRIMARY KEY (id),
					UNIQUE KEY guid (guid)
				);'
			);
			// Log a table creation event
			EventLog::log('Installed feedlist cache table.');
			// Add a periodical execution event to be triggered hourly
			CronTab::add_hourly_cron( 'feedlist', 'load_feeds', 'Load feeds for feedlist plugin.' );
			// Log the cron creation event
			EventLog::log('Added hourly cron for feed updates.');			
		}
	}

	/**
	 * Plugin plugin_deactivation action, executes when any plugin is deactivated.
	 * @param string $plugin_id The filename of the plguin that was deactivated.
	 */
	public function action_plugin_deactivation( $file )
	{
		// Was this plugin deactivated?
		if(realpath($file) == md5(__FILE__)) {
			// Drop the database table
			DB::query( 'DROP TABLE IF EXISTS ' . DB::table('feedlist') . ';');
			// Log a dropped table event
			EventLog::log('Removed feedlist cache table.');
			// Remove the periodical execution event
			CronTab::delete_cronjob( 'feedlist' );
			// Log the cron deletion event.
			EventLog::log('Deleted cron for feed updates.');			
		}
	}
	
	/**
	 * Executes when the admin plugins page wants to know if plugins have configuration links to display.
	 * 
	 * @param array $actions An array of existing actions for the specified plugin id. 
	 * @param string $plugin_id A unique id identifying a plugin.
	 * @return array An array of supported actions for the named plugin
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		// Is this plugin the one specified?
		if($plugin_id == $this->plugin_id()) {
			// Add a 'configure' action in the admin's list of plugins
			$actions[]= 'configure';
		}
		return $actions;
	}
	
	/**
	 * Executes when the admin plugins page wants to display the UI for a particular plugin action.
	 * Displays the plugin's UI.
	 * 
	 * @param string $plugin_id The unique id of a plugin
	 * @param string $action The action to display
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		// Display the UI for this plugin?
		if($plugin_id == $this->plugin_id()) {
			// Depending on the action specified, do different things
			switch($action) {
			// For the action 'configure':
			case 'configure':
				// Create a new Form called 'feedlist'
				$ui = new FormUI( 'feedlist' );
				// Add a text control for the feed URL
				$feedurl= $ui->add('textmulti', 'feedurl', 'Feed URL');
				// Mark the field as required
				$feedurl->add_validator('validate_required');
				// Mark the field as requiring a valid URL
				$feedurl->add_validator('validate_url');
				// When the form is successfully completed, call $this->updated_config()
				$ui->on_success(array($this, 'updated_config'));
				// Display the form
				$ui->out();
				break;
			}
		}
	}
	
	/**
	 * Perform actions when the admin plugin form is successfully submitted. 
	 * 
	 * @param FormUI $ui The form that successfully completed
	 * @return boolean True if the normal processing should occur to save plugin options from the form to the database
	 */
	public function updated_config( $ui )
	{
		// Delete the cached feed data
		DB::query('TRUNCATE ' . DB::table('feedlist'));
		
		// Reset the cronjob so that it runs immediately with the change
		CronTab::delete_cronjob( 'feedlist' );
		CronTab::add_hourly_cron( 'feedlist', 'load_feeds', 'Load feeds for feedlist plugin.' );
		// Return true to allow default form processing to save form details to the options table
		return true;  
	} 
	
	/**
	 * Plugin add_template_vars action, executes just before a template is to be rendered.
	 * Note that $theme and $handler_vars are passed by reference, and so you can add things to them.
	 * @param Theme $theme The theme object that is displaying the template.
	 * @param array $handler_vars Variables passed in the URL to the action handler.
	 */   
	public function action_add_template_vars( $theme, $handler_vars )
	{
		// Get the most recent ten items from each feed
		$feedurls= Options::get('feedlist:feedurl');
		$feeds= array();
		$feeditems= array();
		foreach($feedurls as $index=>$feedurl) {
			$items = DB::get_results('SELECT * FROM ' . DB::table('feedlist') . ' WHERE feed_id = ? ORDER BY updated DESC LIMIT 10', array($index));
			
			// If there are items to display, produce output
			if(count($items)) {
				$feed= "<ul>\n";
				
				foreach ( $items as $item ) {
					$feed.= sprintf( 
						"\t" . '<li><a href="%1$s">%2$s</a></li>' . "\n", 
						$item->link, 
						$item->title
					);
				}
				
				$feed.= "</ul>\n";
			}
			else {
				$feed= '<p>Sorry, no items to display.</p>';
			}
			$feeds[] = $feed;	
			$feeditems = array_merge($feeditems, $items);
		}
		// Assign the output to the template variable $feedlist
		/*  <? echo $feedlist[0];  ? >  */// This will output the first feed list in the template 
		$theme->assign( 'feedlist', $feeds );
		$theme->assign( 'feeditems', $feeditems ); 
	}

	/**
	 * Plugin load_feeds filter, executes for the cron job defined in action_plugin_activation()
	 * @param boolean $result The incoming result passed by other sinks for this plugin hook
	 * @return boolean True if the cron executed successfully, false if not.
	 */ 	
	public function filter_load_feeds( $result )
	{
		$feedurls = Options::get('feedlist:feedurl');
		foreach($feedurls as $feed_id=>$feedurl) {

			// Fetch the feed from remote into an XML object
			if($feedurl == '') {
				EventLog::log("Could not update feed {$feedurl}", 'warning');
			}
			else {
				$xml = simplexml_load_string( RemoteRequest::get_contents( $feedurl ) );
				$channel = $xml->channel;
				// If there are feed items, cache them to the feedlist table
				if ( isset( $channel->item ) ) {
					foreach($channel->item as $item) {
						if(isset($item->guid)) {
							$guid = $item->guid;
						}
						else {
							$guid = md5($item->asXML());
						}
						DB::query('
							INSERT IGNORE INTO ' . DB::table('feedlist') . ' (feed_id, guid, title, link, updated, description) 
							VALUES (?, ?, ?, ?, NOW(), ?);',
							array(
								$feed_id,
								$guid,
								$item->title,
								$item->link,
								$item->description
							) 
						);
					}
				}
				// Log an event that the feed was updated
				EventLog::log("Updated feed {$feedurl}");
			}
		}
		$olddate = DB::get_value('SELECT updated FROM ' . DB::table('feedlist') . ' ORDER BY updated DESC LIMIT 10, 1');
		DB::query('DELETE FROM ' . DB::table('feedlist') . ' WHERE updated < ?', array($olddate));
		
		return $result;  // Only change a cron result to false when it fails.
	}
	
	public function xmlrpc_system__testme($input)
	{
		return $input[0];
	}
}	

?>
