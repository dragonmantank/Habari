<?php


/**
* YouTube Silo
*/

class YouTubeSilo extends Plugin implements MediaSilo
{
	const SILO_NAME = 'YouTube';

	static $cache = array();

	/**
	* Provide plugin info to the system
	*/
	public function info()
	{
		return array('name' => 'YouTube Media Silo',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Implements basic YouTube integration',
			'copyright' => '2008',
			);
	}

	/**
	* Initialize some internal values when plugin initializes
	*/
	public function action_init()
	{
	}

	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*/
	public function silo_info()
	{
		return array('name' => self::SILO_NAME);
	}

	/**
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_dir($path)
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . PATH_SEPARATOR . 'Zend');
		require_once 'Zend/Loader.php';
		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		$youtube = new Zend_Gdata_YouTube();
		$props = array();
		$props['filetype']= 'youtube';

		$results = array();
		$section = strtok($path, '/');
		// TODO remove redundant code - possibly put the calls in a YouTube class?
		switch($section) {
			case 'videos':
				$videoFeed = $youtube->getUserUploads(Options::get(strtolower(get_class($this)) . ':username_' . User::identify()->id));
				foreach ($videoFeed as $videoEntry) {

					$props['url']= $this->findFlashUrl($videoEntry);
					$props['thumbnail_url'] = $videoEntry->mediaGroup->thumbnail[0]->url;
					$props['title']= $videoEntry->mediaGroup->title->text;
					$props['description']= $videoEntry->mediaGroup->description->text;

					$results[] = new MediaAsset(
						self::SILO_NAME . '/videos/' . $videoEntry->getVideoId(),
						false,
						$props
					);
				}
				break;
			case 'tags':
				$videoFeed = $youtube->getSubscriptionFeed(Options::get(strtolower(get_class($this)) . ':username_' . User::identify()->id));
				foreach ($videoFeed as $videoEntry) {

					$props['url']= $this->findFlashUrl($videoEntry);
					$props['thumbnail_url'] = $videoEntry->mediaGroup->thumbnail[0]->url;
					$props['title']= $videoEntry->mediaGroup->title->text;
					$props['description']= $videoEntry->mediaGroup->description->text;

					$results[] = new MediaAsset(
						self::SILO_NAME . '/videos/' . $videoEntry->getVideoId(),
						false,
						$props
					);
				}
				break;
			case 'favorites':
				$videoFeed = $youtube->getUserFavorites(Options::get(strtolower(get_class($this)) . ':username_' . User::identify()->id));
				foreach ($videoFeed as $videoEntry) {

					$props['url']= $this->findFlashUrl($videoEntry);
					$props['thumbnail_url'] = $videoEntry->mediaGroup->thumbnail[0]->url;
					$props['title']= $videoEntry->mediaGroup->title->text;
					$props['description']= $videoEntry->mediaGroup->description->text;

					$results[] = new MediaAsset(
						self::SILO_NAME . '/videos/' . $videoEntry->getVideoId(),
						false,
						$props
					);
				}
				break;
			case '':
				$results[] = new MediaAsset(
					self::SILO_NAME . '/videos',
					true,
					array('title' => 'Videos')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/tags',
					true,
					array('title' => 'Tags')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/favorites',
					true,
					array('title' => 'Favorites')
				);
				break;
		}

		return $results;
	}

	/**
	* Get the file from the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param array $qualities Qualities that specify the version of the file to retrieve.
	* @return MediaAsset The requested asset
	*/
	public function silo_get($path, $qualities = null)
	{
	}

	/**
	* Get the direct URL of the file of the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param array $qualities Qualities that specify the version of the file to retrieve.
	* @return string The requested url
	*/
	public function silo_url($path, $qualities = null)
	{
	}

	/**
	* Create a new asset instance for the specified path
	*
	* @param string $path The path of the new file to create
	* @return MediaAsset The requested asset
	*/
	public function silo_new($path)
	{
	}

	/**
	* Store the specified media at the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param MediaAsset $ The asset to store
	*/
	public function silo_put($path, $filedata)
	{
	}

	/**
	* Delete the file at the specified path
	*
	* @param string $path The path of the file to retrieve
	*/
	public function silo_delete($path)
	{
	}

	/**
	* Retrieve a set of highlights from this silo
	* This would include things like recently uploaded assets, or top downloads
	*
	* @return array An array of MediaAssets to highlihgt from this silo
	*/
	public function silo_highlights()
	{
	}

	/**
	* Retrieve the permissions for the current user to access the specified path
	*
	* @param string $path The path to retrieve permissions for
	* @return array An array of permissions constants (MediaSilo::PERM_READ, MediaSilo::PERM_WRITE)
	*/
	public function silo_permissions($path)
	{
	}

	/**
		* Returns true if plugin config form values defined in action_plugin_ui should be stored in options by Habari
		* @return boolean True if options should be stored
		**/
	public function updated_config($ui)
	{
		// Save the submitted values
		return true;
	}

	/**
	* Add actions to the plugin page for this plugin
	* The authorization should probably be done per-user.
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()){
			$actions[] = 'Configure';
		}

		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()){
			switch ($action){
				case 'Configure':
					$ui= new FormUI( strtolower( get_class( $this ) ) );
					$username= $ui->add('text', 'username_' . User::identify()->id, 'YouTube Username:');
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					break;
			}
		}
	}

	public function action_admin_footer( $theme ) {
		// Add the media type 'youtube' if this is the publish page
		// TODO test media addition
		if ( $theme->admin_page == 'publish' ) {
			echo <<< YOUTUBE
			<script type="text/javascript">
				habari.media.output.youtube = function(fileindex, fileobj) {
					habari.editor.insertSelection('<object width="425" height="355"><param name="movie" value="' + fileobj.url + '"></param><param name="wmode" value="transparent"></param><embed src="' + fileobj.url + '" type="application/x-shockwave-flash" wmode="transparent" width="425" height="355"></embed></object>');
				}
				habari.media.preview.youtube = function(fileindex, fileobj) {
					var stats = '';
					return '<div class="mediatitle">' + fileobj.title + '</div><img src="' + fileobj.thumbnail_url + '"><div class="mediastats"> ' + stats + '</div>';
				}
			</script>
YOUTUBE;
		}
	}

	/**
	* Finds the URL for the flash representation of the specified video
	*
	* @param Zend_Gdata_YouTube_VideoEntry $entry The video entry
	* @return (string|null) The URL or null, if the URL is not found
	*/
	function findFlashUrl($entry) 
	{
		foreach ($entry->mediaGroup->content as $content) {
			if ($content->type === 'application/x-shockwave-flash') {
				return $content->url;
			}
		}
		return null;
	}

}

?>
