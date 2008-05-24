<?php
 
class PingerUpdate extends Plugin
{

	public function info()
	{
		return array (
			'name' => 'Pinger Update',
			'url' => 'http://redalt.com/Habari/plugins/Pinger+Update',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net',
			'version' => '1.0',
			'description' => 'Sends pings to notification services.',
			'license' => 'Apache License 2.0',
		);
	}

	public function action_post_status_published($post)
	{
		if ( $post->status == Post::status( 'published' ) ) {
			CronTab::add_single_cron( 'ping update sites', 'ping_sites', time(), 'Ping update sites.' );
		}
	}

	public function filter_ping_sites($result)
	{
		$ping = RemoteRequest::get_contents('http://www.pingoat.com/index.php?pingoat=go&blog_name=' . urlencode(Options::get('title')) . '&blog_url=' . urlencode(Site::get_url('habari')) . '&rss_url=' . urlencode(URL::get('collection', array('index'=>1))) . '&cat_0=1&id%5B%5D=0&id%5B%5D=1&id%5B%5D=2&id%5B%5D=3&id%5B%5D=4&id%5B%5D=5&id%5B%5D=6&id%5B%5D=7&id%5B%5D=8&id%5B%5D=9&id%5B%5D=10&id%5B%5D=11&id%5B%5D=12&id%5B%5D=13&id%5B%5D=14&id%5B%5D=15&id%5B%5D=16&id%5B%5D=17&id%5B%5D=18&id%5B%5D=19&id%5B%5D=20&id%5B%5D=21&id%5B%5D=22&id%5B%5D=23&id%5B%5D=24&id%5B%5D=25&cat_1=0&cat_2=0');
		EventLog::log('Ping sent to Pingoat.', 'info', 'default', null, $ping );
		return $result;
	}
}


?>
