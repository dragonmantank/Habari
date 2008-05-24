<?php
class Sitemaps extends Plugin {

	/**
	 * Returns required plugin informations
	 */
	public function info() {
		return array(
			'name' => 'Sitemaps',
			'version' => '0.6',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Sitemaps plugin for Habari.',
			'copyright' => '2007'
		);
	}
	
	/**
	 * Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules.
	 *
	 * Call `Sitemaps::act('Sitemap')` when a request for `sitemap.xml` is received.
	 *
	 * @param array $db_rules Array of rewrite rules compiled so far
	 * @return array Modified rewrite rules array, we added our custom rewrite rule
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '"sitemap.xml"', 'Sitemaps', 'Sitemap' );
		return $db_rules;
	}
	
	/**
	 * Act function called by the `Controller` class.
	 * Dispatches the request to the proper action handling function.
	 *
	 * @param string $action Action called by request, we only support 'Sitemap'
	 */
	public function act( $action )
	{
		switch ( $action )
		{
			case 'Sitemap':
				self::Sitemap();
				break;
		}
	}
	
	/**
	 * Sitemap function called by the self `act` function.
	 * Generates the `sitemap.xml` file to output.
	 */
	public function Sitemap()
	{
		//return cached sitemap if exsist
		if ( Cache::has( 'sitemap' ) ){
			$xml= Cache::get( 'sitemap' );
		}
		else {
			//..or generate a new one
			$xml= '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
	
			$xml= new SimpleXMLElement( $xml );
					
			// Retreive all published posts and pages from the database
			$content['posts']= Posts::get( array( 'content_type' => 'entry', 'status' => Post::status( 'published' ), 'nolimit' => 1 ) );
			$content['pages']= Posts::get( array( 'content_type' => 'page', 'status' => Post::status( 'published' ), 'nolimit' => 1 ) );
			
			// Add the index page first
			$url= $xml->addChild( 'url' );
			$url_loc= $url->addChild( 'loc', Site::get_url( 'habari' ) );
			
			// Generate the `<url>`, `<loc>`, `<lastmod>` markup for each post and page.
			foreach ( $content as $entries ) {
				foreach ( $entries as $entry ) {
					$url= $xml->addChild( 'url' );
					$url_loc= $url->addChild( 'loc', $entry->permalink );
					$url_lastmod= $url->addChild( 'lastmod', Utils::atomtime( $entry->updated ) );
				}
			}
			$xml= $xml->asXML();
			Cache::set('sitemap', $xml);
		}
		
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/xml' );
		print $xml;
	}
	
	/**
	 * post_update_status action called when the post status field is updated.
	 * Expires the cached sitemap if the status has been changed.
	 * 
	 * @param Post $post The post being updated
	 * @param string $new_status the status feild new value
	 */
	public function action_post_update_status( $post, $new_status )
	{	
		if ($post->status != $new_status){
			Cache::expire('sitemap');
		}
	}

}
?>
