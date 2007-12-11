<?php
class flickrAPI
{
	function __construct()
	{
		$this->key = 'cd0ae46b1332aa2bd52ba3063f0db41c';
		$this->secret = '76cf747f70be9029';
		$this->endpoint = 'http://flickr.com/services/rest/?';
		$this->authendpoint = 'http://flickr.com/services/auth/?';
		$this->uploadendpoint = 'http://www.flickr.com/services/upload/';
	}

	public function sign($args)
	{
		ksort($args);
		$a = '';
		foreach($args as $key => $value){
			$a .= $key . $value;
		}
		return md5($this->secret . $a);
	}

	public function encode($params)
	{
		$encoded = array();
		foreach ($params as $k => $v){
			$encoded[] = urlencode($k) . '=' . urlencode($v);
		}
		return $encoded;
	}

	function call($method, $args = array ())
	{
		$args = array_merge(array ('method' => $method,
				'api_key' => $this->key),
			$args);
		ksort($args);

		$auth_sig = '';
		foreach ($args as $key => $data){
			$auth_sig .= $key . $data;
		}

		$api_sig = md5($this->secret . $auth_sig);

		$args = array_merge($args, array ('api_sig' => $api_sig));
		ksort($args);

		$url = 'http://www.flickr.com/services/rest/?' . implode('&', $this->encode($args));

		$call = new RemoteRequest($url);

		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)){
			throw $result;
		}
		$response = $call->get_response_body();
		$xml = new SimpleXMLElement($response);
		return $xml;
	}
}

class Flickr extends flickrAPI
{
	function __construct($params = array())
	{
		parent::__construct($params);
	}
	// URL building
	function getPhotoURL($p, $size = 'm', $ext = 'jpg')
	{
		return "http://static.flickr.com/{$p['server']}/{$p['id']}_{$p['secret']}_{$size}.{$ext}";
	}
	// authentication and approval
	public function getFrob()
	{
		$xml = $this->call('flickr.auth.getFrob', array());
		return $xml->frob;
	}

	public function authLink($frob)
	{
		$params['api_key'] = $this->key;
		$params['frob'] = $frob;
		$params['perms'] = 'write';
		$params['api_sig'] = md5($this->secret . 'api_key' . $params['api_key'] . 'frob' . $params['frob'] . 'permswrite');
		$link = $this->authendpoint . implode('&', $this->encode($params));
		return $link;
	}

	function getToken($frob)
	{
		$xml = $this->call('flickr.auth.getToken', array('frob' => $frob));
		return $xml;
	}
	// get publicly available photos
	function getPublicPhotos($nsid, $extras = '', $per_page = '', $page = '')
	{
		$params = array('user_id' => $nsid);
		if($extras){
			$params['extras'] = $extras;
		}
		if($per_page){
			$params['per_page'] = $per_page;
		}
		if($page){
			$params['page'] = $page;
		}

		$xml = $this->call('flickr.people.getPublicPhotos' , $params);
		foreach($xml->photos->attributes() as $key => $value){
			$pic[$key] = (string)$value;
		}
		$i = 0;
		foreach($xml->photos->photo as $photo){
			foreach($photo->attributes() as $key => $value){
				$pic['photos'][(string)$photo['id']][$key] = (string)$value;
			}
			$i++;
		}
		return $pic;
	}
	// Photosets methods
	function photosetsGetList($nsid = '')
	{
		$params = array();
		if($this->token){
			$params['auth_token'] = $this->token;
		}

		if($nsid){
			$params['user_id'] = $nsid;
		}

		$xml = $this->call('flickr.photosets.getList', $params);
		if(!$xml){
			return false;
		}
		foreach($xml->photosets->attributes() as $key => $value){
			$ret[$key] = (string)$value;
		}
		$i = 0;
		foreach($xml->photosets->photoset as $key => $value){
			foreach($value->attributes() as $kk => $vv){
				$ret['photosets'][(string)$value['id']][$kk] = (string)$vv;
			}

			foreach($value->children() as $kk => $vv){
				$ret['photosets'][(string)$value['id']][$kk] = (string)$vv;
			}
			$i++;
		}
		return $ret;
	}

	function photosetsGetInfo($photoset_id)
	{
		$params = array('photoset_id' => $photoset_id);
		$xml = $this->call('flickr.photosets.getInfo', $params);
		if(!$xml){
			return false;
		}
		foreach($xml->photoset->attributes() as $key => $value){
			$ret[$key] = (string)$value;
		}
		foreach($xml->photoset as $key => $value){
			$ret[$key] = $value;
		}
		return $ret;
	}

	function photosetGetPrimary($p, $size = 'm', $ext = '.jpg')
	{
		return 'http://static.flickr.com/' . $p['server'] . '/' . $p['primary'] . '_' . $p['secret'] . '_' . $size . $ext;
	}

	function photosetsGetPhotos($photoset_id)
	{
		$params = array('photoset_id' => $photoset_id);
		$xml = $this->call('flickr.photosets.getPhotos', $params);
		if(!$xml){
			return false;
		}
		foreach($xml->photoset->attributes() as $key => $value){
			$ret[$key] = (string)$value;
		}
		$i = 0;
		foreach($xml->photoset->photo as $photo){
			foreach($photo->attributes() as $key => $value){
				$ret['photos'][(string)$photo['id']][$key] = (string)$value;
			}
			$i++;
		}
		return $ret;
	}

	function reflectionGetMethods()
	{
		$params = array();
		$xml = $this->call('flickr.reflection.getMethods', $params);
		if(!$xml){
			return false;
		}
		$ret = (array)$xml->methods->method;
		return $ret;
	}
}

/**
* Flickr Silo
*/

class FlickrSilo extends Plugin implements MediaSilo
{
	const SILO_NAME = 'flickr';

	/**
	* Provide plugin info to the system
	*/
	public function info()
	{
		return array('name' => 'Flickr Media Silo',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'desccription' => 'Implements basic Flickr integration',
			'copyright' => '2007',
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
	*    name- The name of the silo, used as the root directory for media in this silo
	*/
	public function silo_info()
	{
		return array('name' => self::SILO_NAME,
			);
	}

	/**
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_dir($path)
	{
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
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_contents()
	{
		$flickr = new Flickr();
		$photos = $flickr->GetPublicPhotos('45643934@N00', null, 5);
		foreach($photos['photos'] as $photo){
			$url = $flickr->getPhotoURL($photo);
			echo '<img src="' . $url . '" width="150px">';
		}
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
			$flickr_ok = $this->is_auth();

			if($flickr_ok) {
			}
			else {
				$actions[] = 'Authorize';
			}
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
				case 'Authorize':
					if($this->is_auth()) {
						echo "<p>You have already successfully authorized Habari to access your Flickr account.</p>";
					}
					else {

						$flickr = new Flickr();
						$_SESSION['flickr_frob'] = '' . $flickr->getFrob();
						$auth_url = $flickr->authLink($_SESSION['flickr_frob']);
						$confirm_url = URL::get('admin', array('page' => 'plugins', 'configure' => $this->plugin_id(), 'action' => 'confirm')) . '#plugin_options';
						echo <<< END_AUTH

<p>To use this plugin, you must <a href="{$auth_url}" target="_blank">authorize Habari to have access to your Flickr account</a>.
<p>When you have completed the authorization on Flickr, return here and <a href="$confirm_url">confirm that the authorization was successful</a>.

END_AUTH;
					}
					break;

				case 'confirm':
					$flickr = new Flickr();
					if(!isset($_SESSION['flickr_frob'])) {
						$auth_url = URL::get('admin', array('page' => 'plugins', 'configure' => $this->plugin_id(), 'action' => 'Authorize')) . '#plugin_options';
						echo '<p>Either you have already authorized Habari to access your flickr account, or you have not yet done so.  Please <a href="' . $auth_url . '">try again</a>.</p>';
					}
					else {
						$token = $flickr->getToken($_SESSION['flickr_frob']);
						if(isset($token->auth->perms)) {
							Options::set('flickr_token_' . User::identify()->id, '' . $token->auth->token);
							echo '<p>Your authorization was set successfully.</p>';
						}
						else {
							echo '<p>There was a problem with your authorization:</p>';
							echo htmlspecialchars($token->asXML());
						}
						unset($_SESSION['flickr_frob']);
					}
					break;
			}
		}
	}

	private function is_auth()
	{
		static $flickr_ok = null;
		if(isset($flickr_ok)) {
			return $flickr_ok;
		}

		$flickr_ok = false;
		$token = Options::get('flickr_token_' . User::identify()->id);
		if($token != '') {
			$flickr = new Flickr();
			$result = $flickr->call('flickr.auth.checkToken', array('api_key' => $flickr->key, 'auth_token'=>$token));
			if(isset($result->auth->perms)) {
				$flickr_ok = true;
			}
			else {
				Options::set('flickr_token_' . User::identify()->id);
				unset($_SESSION['flickr_token']);
			}
		}
		return $flickr_ok;
	}
}

?>