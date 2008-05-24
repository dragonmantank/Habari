<?php

/**
 * Keep Login plugin
 *
 **/

class KeepLogin extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Keep Login',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Uses ajax to keep a session logged in in the admin.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Produce output in the admin header
	 * Adds the necessary javascript to make periodic ajax calls to the admin.
	 */	 
	public function action_admin_header( $admintheme )
	{
		$ajaxurl = URL::get('auth_ajax', array('context'=>'keep_session'));
		echo <<< HEADER_JS
<script type="text/javascript">
$(document).ready(function(){
	window.setInterval(
		function(){
			$.post('$ajaxurl');
		},
		1000 * 60 * 5 // 5 minutes
	);
});
</script>
HEADER_JS;
	}

	public function action_auth_ajax_keep_session()
	{
		echo time();
	}

}

?>