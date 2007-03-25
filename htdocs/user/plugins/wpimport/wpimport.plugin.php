<?php

define('IMPORT_BATCH', 100);


/**
 * WordPress Importer - Imports data from WordPress into Habari
 *
 * @package Habari
 */
class WPImporter extends Plugin implements Importer
{
	private $supported_importers = array();

	public function __construct()
	{
		$this->supported_importers = array(_t('WordPress Database'));
	}

	/**
	* Return plugin metadata for this plugin
	*
	* @return array Plugin metadata
	*/
	public function info()
	{
		return array();
	}

	/**
	 * Return a list of names of things that this importer imports
	 *
	 * @return array List of importables.
	 */
	public function filter_import_names($import_names)
	{
		return array_merge($import_names, $this->supported_importers);
	}

	/**
	 * Plugin filter that supplies the UI for the WP importer
	 *
	 * @param string $stageoutput The output stage UI
	 * @param string $import_name The name of the selected importer
	 * @param string $stage The stage of the import in progress
	 * @param string $step The step of the stage in progress
	 * @return output for this stage of the import
	 */
	public function filter_import_stage($stageoutput, $import_name, $stage, $step)
	{
		// Only act on this filter if the import_name is one we handle...
		if(!in_array($import_name, $this->supported_importers)) {
			// Must return $stageoutput as it may contain the stage HTML of another importer
			return $stageoutput;
		}

		$inputs = array();

		// Validate input from various stages...
		switch($stage) {
		case 1:
			if( isset($_POST)) {
				$valid_fields= array('db_name','db_host','db_user','db_pass','db_prefix');
				$inputs= array_intersect_key($_POST, array_flip($valid_fields));
				if($this->wp_connect($inputs['db_host'], $inputs['db_name'], $inputs['db_user'], $inputs['db_pass'], $inputs['db_prefix'])) {
					$stage = 2;
				}
				else {
					$inputs['warning'] = 'The values supplied could not connect to the WordPress database.  Please correct them and try again.';
				}
			}
			break;
		}

		// Based on the stage of the import we're on, do different things...
		switch($stage) {
		case 1:
		default:
			$output = $this->stage1($inputs);
			break;
		case 2:
			$output = $this->stage2($inputs);
		}

		return $output;
	}

	/**
	 * Create the UI for stage one of the WP import process
	 *
	 * @param array $inputs Inputs received via $_POST to the importer
	 * @return string The UI for the first stage of the import process
	 */
	private function stage1($inputs)
	{
		$default_values = array(
			'db_name' => '',
			'db_host' => 'localhost',
			'db_user' => '',
			'db_pass' => '',
			'db_prefix' => 'wp_',
			'warning' => '',
		);
		$inputs = array_merge($default_values, $inputs);
		extract($inputs);
		if($warning != '') {
			$warning = "<p class=\"warning\">{$warning}</p>";
		}
		$output = <<< WP_IMPORT_STAGE1
			<p>Habari will attempt to import from a WordPress Database.</p>
			{$warning}
			<p>Please provide the connection details for an existing WordPress database:</p>
			<table>
				<tr><td>Database Name</td><td><input type="text" name="db_name" value="{$db_name}"></td></tr>
				<tr><td>Database Host</td><td><input type="text" name="db_host" value="{$db_host}"></td></tr>
				<tr><td>Database User</td><td><input type="text" name="db_user" value="{$db_user}"></td></tr>
				<tr><td>Database Password</td><td><input type="password" name="db_pass" value="{$db_pass}"></td></tr>
				<tr><td>Table Prefix</td><td><input type="text" name="db_prefix" value="{$db_prefix}"></td></tr>
			</table>
			<input type="hidden" name="stage" value="1">
			<p class="submit"><input type="submit" name="import" value="Import" /></p>

WP_IMPORT_STAGE1;
		return $output;
	}

	/**
	 * Create the UI for stage two of the WP import process
	 * This stage kicks off the ajax import.
	 *
	 * @param array $inputs Inputs received via $_POST to the importer
	 * @return string The UI for the second stage of the import process
	 */
	private function stage2($inputs)
	{
		extract($inputs);

		$ajax_url = URL::get('auth_ajax', array('context'=>'wp_import_posts'));

		$output = <<< WP_IMPORT_STAGE2
			<p>Import In Progress</p>
			<div id="import_progress">Starting Import...</div>
			<script type="text/javascript">
			// A lot of ajax stuff goes here.
			$(document).ready(function(){
				$('#import_progress').load(
					"{$ajax_url}",
					{
						db_host: "{$db_host}",
						db_name: "{$db_name}",
						db_user: "{$db_user}",
						db_pass: "{$db_pass}",
						db_prefix: "{$db_prefix}",
						postindex: 0
					}
				);
			});
			</script>
WP_IMPORT_STAGE2;
		return $output;
	}

	/**
	 * Attempt to connect to the WordPress database
	 *
	 * @param string $db_host The hostname of the WP database
	 * @param string $db_name The name of the WP database
	 * @param string $db_user The user of the WP database
	 * @param string $db_pass The user's password for the WP database
	 * @param string $db_prefix The table prefix for the WP instance in the database
	 * @return mixed false on failure, DatabseConnection on success
	 */
	private function wp_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix)
	{
		// Connect to the database or return false
		try {
			$wpdb= new DatabaseConnection();
			$wpdb->connect( "mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass, $db_prefix );
			return $wpdb;
		}
		catch( Exception $e) {
			return false;
		}
	}

	/**
	 * The plugin sink for the auth_ajax_wp_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_wp_import_posts($handler)
	{
		$valid_fields= array('db_name','db_host','db_user','db_pass','db_prefix','postindex');
		$inputs= array_intersect_key($_POST, array_flip($valid_fields));
		extract($inputs);
		$wpdb = $this->wp_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix);
		if($wpdb) {
			$postcount = $wpdb->get_value("SELECT count(id) FROM {$db_prefix}posts;");
			$min = $postindex * IMPORT_BATCH + 1;
			$max = min(($postindex + 1) * IMPORT_BATCH, $postcount);

			echo "<p>Importing posts {$min}-{$max} of {$postcount}.</p>";
			$posts= $wpdb->get_results("
				SELECT
					post_content as content,
					ID as id,
					post_title as title,
					post_name as slug,
					post_author as user_id,
					guid as guid,
					post_date as pubdate,
					post_modified as updated,
					post_status,
					post_type
				FROM {$db_prefix}posts
				ORDER BY ID DESC
				LIMIT {$min}, " . IMPORT_BATCH
				, array(), 'Post');

			$post_map= array();
			foreach( $posts as $post ) {

				$tags= $wpdb->get_column(
					"SELECT category_nicename
					FROM {$db_prefix}post2cat
					INNER JOIN {$db_prefix}categories
					ON ({$db_prefix}categories.cat_ID= {$db_prefix}post2cat.category_id)
					WHERE post_id= {$post->id}"
				);

				$post_array= $post->to_array();
				switch($post_array['post_status']) {
				case 'publish':
					$post_array['status']= Post::status('published');
					break;
				default:
					$post_array['status']= Post::status($post_array['post_status']);
					break;
				}
				unset($post_array['post_status']);

				switch($post_array['post_type']) {
				case 'post':
					$post_array['content_type']= Post::type('entry');
					break;
				case 'page':
					$post_array['content_type']= Post::type('page');
					break;
				default:
					// We're not inserting WP's media records.  That would be silly.
					continue;
				}
				unset($post_array['post_type']);

				$p= new Post( $post_array );
				$p->slug= $post->slug;
				$p->guid= $p->guid; // Looks fishy, but actually causes the guid to be set.
				$p->tags= $tags;

				try {
					$p->insert();
				}
				catch( Exception $e ) {
					Utils::debug($p);
				}
				$p->info->wp_id = $post_array['id'];  // Store the WP post id in the post_info table for later
			}
			if($max < $postcount) {
				$ajax_url= URL::get('auth_ajax', array('context'=>'wp_import_posts'));
				$postindex++;

				echo <<< WP_IMPORT_AJAX1
					<script type="text/javascript">
					$('#import_progress').load(
						"{$ajax_url}",
						{
							db_host: "{$db_host}",
							db_name: "{$db_name}",
							db_user: "{$db_user}",
							db_pass: "{$db_pass}",
							db_prefix: "{$db_prefix}",
							postindex: {$postindex}
						}
					);

				</script>
WP_IMPORT_AJAX1;
			}
			else {
				$ajax_url = URL::get('auth_ajax', array('context'=>'wp_import_comments'));

				echo <<< WP_IMPORT_AJAX2
					<script type="text/javascript">
					$('#import_progress').load(
						"{$ajax_url}",
						{
							db_host: "{$db_host}",
							db_name: "{$db_name}",
							db_user: "{$db_user}",
							db_pass: "{$db_pass}",
							db_prefix: "{$db_prefix}",
							commentindex: 0
						}
					);

				</script>
WP_IMPORT_AJAX2;

			}
		}
		else {
			echo '<p>'._t('The database connection details have failed to connect.').'</p>';
		}
	}

	/**
	 * The plugin sink for the auth_ajax_wp_import_comments hook.
	 * Responds via authenticated ajax to requests for comment importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_wp_import_comments($handler)
	{
		$valid_fields= array('db_name','db_host','db_user','db_pass','db_prefix','commentindex');
		$inputs= array_intersect_key($_POST, array_flip($valid_fields));
		extract($inputs);
		$wpdb = $this->wp_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix);
		if($wpdb) {
			$commentcount = $wpdb->get_value("SELECT count(comment_ID) FROM {$db_prefix}comments;");
			$min = $commentindex * IMPORT_BATCH + 1;
			$max = min(($commentindex + 1) * IMPORT_BATCH, $commentcount);

			echo "<p>Importing comments {$min}-{$max} of {$commentcount}.</p>";

			$postinfo = DB::table('postinfo');
			$post_info= DB::get_results("SELECT post_id, value FROM {$postinfo} WHERE name = 'wp_id';");
			foreach($post_info as $info) {
				$post_map[$info->value] = $info->post_id;
			}

			$comments= $wpdb->get_results("
				SELECT
				comment_content as content,
				comment_author as name,
				comment_author_email as email,
				comment_author_url as url,
				INET_ATON(comment_author_IP) as ip,
			 	comment_approved as status,
				comment_date as date,
				comment_type as type,
				ID as wp_post_id
				FROM {$db_prefix}comments
				INNER JOIN
				{$db_prefix}posts on ({$db_prefix}posts.ID= {$db_prefix}comments.comment_post_ID)
				LIMIT {$min}, " . IMPORT_BATCH
				, array(), 'Comment');

			foreach( $comments as $comment ) {
				switch( $comment->type ) {
					case 'pingback': $comment->type= Comment::PINGBACK; break;
					case 'trackback': $comment->type= Comment::TRACKBACK; break;
					default: $comment->type= Comment::COMMENT;
				}

				$carray= $comment->to_array();
				if ($carray['ip'] == '') {
					$carray['ip']= 0;
				}
				switch( $carray['status'] ) {
				case '0':
					$carray['status']= Comment::STATUS_UNAPPROVED;
					break;
				case '1':
					$carray['status']= Comment::STATUS_APPROVED;
					break;
				case 'spam':
					$carray['status']= Comment::STATUS_SPAM;
					break;
				}
				if ( !isset($post_map[$carray['wp_post_id']]) ) {
					Utils::debug($carray);
				}
				else {
					$carray['post_id']= $post_map[$carray['wp_post_id']];
					unset($carray['wp_post_id']);

					$c= new Comment( $carray );
					//Utils::debug( $c );
					$c->insert();
				}
			}

			if($max < $commentcount) {
				$ajax_url= URL::get('auth_ajax', array('context'=>'wp_import_comments'));
				$commentindex++;

				echo <<< WP_IMPORT_AJAX1
					<script type="text/javascript">
					$('#import_progress').load(
						"{$ajax_url}",
						{
							db_host: "{$db_host}",
							db_name: "{$db_name}",
							db_user: "{$db_user}",
							db_pass: "{$db_pass}",
							db_prefix: "{$db_prefix}",
							commentindex: {$commentindex}
						}
					);

				</script>
WP_IMPORT_AJAX1;
			}
			else {
				echo _t('<p>Import is complete.</p>');
			}
		}
		else {
			echo '<p>'._t('The database connection details have failed to connect.').'</p>';
		}
	}

}

?>