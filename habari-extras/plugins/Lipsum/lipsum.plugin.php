<?php

/**
 * Lipsum Plugin Class
 *
 **/

class Lipsum extends Plugin
{
	private $thumbs = array();

	/**
	 * Return information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Lipsum',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Creates random sample data to test themes and plugins',
			'license' => 'Apache License 2.0',
		);
	}
	
	/**
	 * Adds a Configure action to the plugin
	 * 
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The id of a plugin
	 * @return array The array of actions
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= 'Configure';		
		}
		return $actions;
	}
	
	/**
	 * Creates a UI form to handle the plugin configuration
	 *
	 * @param string $plugin_id The id of a plugin
	 * @param array $actions An array of actions that apply to this plugin
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id()==$plugin_id && $action=='Configure' )
			{
			$form= new FormUI( strtolower(get_class( $this ) ) );
			$num_posts= $form->add( 'text', 'num_posts', 'Number of posts to create:', '20');
			$num_posts->add_validator( 'validate_required' );
			$form->on_success( array( $this, 'update_num_posts' ) );
			$form->out();
			}
	}

	function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			set_time_limit(0);
			$user= User::get_by_name( 'lipsum' );
			if ( !$user ) {
				$user = User::create(array (
					'username'=>'lipsum',
					'email'=>'lipsum@example.com',
					'password'=>md5('q' . rand(0,65535)),
				));
			}

			$time = time() - 160;
			
			$num_posts= Options::get(strtolower(get_class($this)) . ':num_posts' );
			$num_posts= ( $num_posts ? $num_post : 20 );

			for($z = 0; $z < $num_posts; $z++) {
				$this->make_post( $user, $time );
			}

			Session::notice('Created 20 sample posts with random comments.');
		}
	}

	function action_plugin_deactivation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			set_time_limit(0);
			$posts = Posts::get(array('info'=>array('lipsum' => true), 'nolimit'=>true));
			$count = 0;
			foreach($posts as $post) {
				$post->delete();
				$count++;
			}
			Session::notice("Removed {$count} sample posts and their comments.");
		}
	}
	
	function update_num_posts( $form )
	{
		$num_posts= $form->num_posts->value;
		$num_posts= is_numeric($num_posts) ? (int) $num_posts : 20;

		$current_count= (int) Posts::get( array( 'info' => array( 'lipsum' => true ), 'count' => true ) );

		if ( $num_posts == $current_count ) {
			return true;
		}
		// remove some posts if the $num_posts is less than the current count
		if ( $num_posts < $current_count ) {
			$limit= $current_count - $num_posts;
			$posts= Posts::get( array( 'info' => array('lipsum' => true), 'limit' => $limit ) );
			$count= 0;
			foreach( $posts as $post) {
				$post->delete();
				$count++;
			}
			Session::notice("Removed {$count} sample posts and their comments.");
		}
		// otherwise, we need to add some posts
		else {
			$user= User::get_by_name( 'lipsum' );
			$time = time() - 160;
			$count= 0;
			for ( $i= $current_count + 1; $i <= $num_posts; $i++ ) {
				$this->make_post( $user, $time );
				$count++;
			}
			Session::notice( "Created {$count} sample posts with random comments.");
		}

		// return true to save num_posts in the options table
		return true;
	}
	
	/**
	 * make_post
	 * Makes a single post and adds a random number of comments (between 0 and 6)
	 * to the post
	 * @param object $user The Lipsum user
	 * @param timestamp $time The published timestamp of the new posts
	 */
	private function make_post( $user, $time )
	{
		$post = Post::create(array(
			'title' => $this->get_title(),
			'content' => $this->get_content(1, 3, 'some', array('thumb'=>1, 'ol'=>1, 'ul'=>1), 'cat'),
			'user_id' => $user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'tags' => 'lipsum',
			'pubdate' => date('Y-m-d H:i:s', $time++),
		));
		$post->info->lipsum = true;
		$post->info->commit();

		$addcomments = rand(0,6);
		for($q = 0; $q < $addcomments; $q++) {
			$comment = Comment::create(array(
				'post_id' => $post->id,
				'name' => $this->num2word(rand(1, 9999)),
				'url' => 'http://example.com/',
				'content' => $this->get_content(1, 2, 'none', array(), 'cat'),
				'status' => Comment::STATUS_APPROVED,
				'type' => Comment::COMMENT,
				'date' => date('Y-m-d H:i:s', $time++),
			));
			$comment->info->lipsum = true;
			$comment->info->commit();
		}
	}

	private function get_pgraph()
	{
		$start = array("Nam quis nulla", "Integer malesuada", "In an enim", "Sed vel lectus", "Donec odio urna,", "Phasellus rhoncus", "Aenean id ", "Vestibulum fermentum", "Pellentesque ipsum",  "Nulla non", "Proin in tellus", "Vivamus luctus", "Maecenas sollicitudin", "Etiam egestas", "Lorem ipsum dolor sit amet,", "Nullam feugiat,", "Aliquam erat volutpat", "Mauris pretium",);
		$mid = array(" a arcu imperdiet", " tempus molestie,", " porttitor ut,", " iaculis quis,", " metus id velit", " lacinia neque", " sed nisl molestie", " sit amet nibh", " consectetuer adipiscing", " turpis at pulvinar vulputate,", " erat libero tristique tellus,", " nec bibendum odio risus"," pretium quam", " ullamcorper nec,", " rutrum non,", " nonummy ac,", " augue id magna",);
		$end = array(" nulla.  "," malesuada.  "," lectus.  "," sem.  "," pulvinar.  "," faucibus fringilla.  "," dignissim sagittis.  "," egestas leo.  "," metus.  "," erat.  "," elit.  "," sit amet ante.  "," volutpat.  "," urna.  "," rutrum.  ",);

		$ipsum_text = '';
		$lines = rand(1,6);
		for($l = 0; $l < $lines; $l++) {
			$line = $start[rand(0,count($start)-1)];
			$mids = rand(1,3);
			for($z = 0; $z < $mids; $z++) $line .= $mid[rand(0,count($mid)-1)];
			$line .= $end[rand(0,count($end)-1)];
			$ipsum_text .= $line;
		}
		$ipsum_text .= "\n\n";
		return $ipsum_text;
	}

	private function num2word($i)
	{
		$word = '';
		$phon = array('do', 're', 'mi', 'fa', 'so', 'la', 'ti', 'ko', 'fu', 'jan');
		do {
			$word = $phon[$i % 10] . $word;
			$i = floor($i/10);
		} while($i >0);
		return $word;
	}

	private function get_title()
	{
		$text = $this->get_pgraph(1);
		$text = strtolower($text);
		$text = preg_replace('/[^a-z\s]/', '', $text);
		$text = explode(' ', $text);
		$words = rand(2, 8);
		$title = '';
		for($i = 0; $i < $words; $i++) {
			$title .= $text[rand(0, count($text)-1)] . ' ';
		}
		$title = ucwords(trim($title));
		return $title;
	}

	private function get_thumb_tag($tags)
	{
		if(count($this->thumbs) == 0) {
			$searchurl = 'http://www.flickr.com/services/rest/?method=flickr.photos.search&api_key=420fb7714e08dbcc97ac8228df21d985&license=4,2&per_page=10&tags=' . urlencode(implode(',', $tags));
			$results = RemoteRequest::get_contents($searchurl);
			preg_match_all('/<photo.*id="([0-9]+)".*owner="([^"]+)".*secret="([0-9a-f]+)".*server="([0-9]+)".*title="([^"]+)".*\/>/', $results, $matches, PREG_SET_ORDER);
			foreach($matches as $match) {
				list($fulltag, $id, $owner, $secret, $server, $title) = $match;
				$imgurl = "http://static.flickr.com/{$server}/{$id}_{$secret}_m.jpg";
				$flickrurl = "http://flickr.com/photos/{$owner}/{$id}";
				$styles = array (
					' style="float:left;"',
					' style="float:right;"',
					' style="display:block;"',
				);
				$style = $styles[rand(0,count($styles)-1)];
				$this->thumbs[] = "<a href=\"{$flickrurl}\"{$style}><img src=\"{$imgurl}\" alt=\"{$title}\"></a>";
			}
		}
		return $this->thumbs[rand(0,count($this->thumbs)-1)];
	}

	private function get_content($min, $max, $more, $features, $imgtags)
	{
		$lipsum_text = '';
		$howmany = rand($min, $max);
		for($i = 0; $i < $howmany; $i++) {
			if(isset($features['thumb'])) {
				if(rand(1, $max - $i + 1) == 1) {
					$lipsum_text .= $this->get_thumb_tag(explode(' ',$imgtags));
					unset($features['thumb']);
				}
			}
			$lipsum_text .= $this->get_pgraph();
			if(isset($features['ol'])) {
				if(rand(1, $max - $i + 1) == 1) {
					$listitems = rand(3,10);
					$lipsum_text .= "<ol>\n";
					for($z = 0; $z < $listitems; $z++) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ol>\n";
					unset($features['ol']);
				}
			}
			if(isset($features['ul'])) {
				if(rand(1, $max - $i + 1) == 1) {
					$listitems = rand(3,10);
					$lipsum_text .= "<ul>\n";
					for($z = 0; $z < $listitems; $z++) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ul>\n";
					unset($features['ul']);
				}
			}

			switch($more) {
			case 'none':
				break;
			case 'some':
				if(rand(1,2) == 1) break;
			case 'all':
				if($i==0 && $howmany > 1) {
					$lipsum_text .= '<!--more-->';
				}
			}
		}
		return $lipsum_text;
	}



}

?>
