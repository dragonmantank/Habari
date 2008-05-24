<?php
class OpenSearch extends Plugin {
		
	/**
	 * Required Plugin Informations
	 */
	public function info() {
		return array(
			'name' => 'OpenSearch 1.1',
			'version' => '0.2',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Add OpenSearch 1.1 support to Habari.',
			'copyright' => '2007'
		);
	}
	
	/**
	 * Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules
	 *
	 * Call `OpenSearch::act('osDescription')` when a request for `content-search.xml` is received
	 *
	 * @param array $db_rules Array of rewrite rules filtered so far
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '"opensearch.xml"', 'OpenSearch', 'osDescription' );
		$db_rules[]= new RewriteRule( array(
			'name' => 'opensearch',
			'parse_regex' => '%^search(?:/(?P<criteria>[^/]+))?(?:/page/(?P<page>\d+))?(?:/count/(?P<count>\d+))?/atom/?$%i',
			'build_str' => 'search(/{$criteria})(/page/{$page})/atom',
			'handler' => 'OpenSearch',
			'action' => 'search',
			'priority' => 8,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Searches posts'
			) );

		return $db_rules;
	}
	
	/**
	 * Assign this plugin to the alternate rules for OpenSearch
	 *
	 * @param array $alternate_rules Rewrite rules assignments for alternate URL
	 */
	public function filter_atom_get_collection_alternate_rules( $alternate_rules )
	{
		$alternate_rules['opensearch']= 'opensearch';
		return $alternate_rules;
	}
	
	/**
	 * Add OpenSearch namespace to the Atom feed
	 *
	 * @param array $xml_namespaces Namespaces currently assigned to this feed
	 */
	public function filter_atom_get_collection_namespaces( $xml_namespaces ) {
		$xml_namespaces['opensearch']= 'http://a9.com/-/spec/opensearch/1.1/';
		return $xml_namespaces;
	}
	
	/**
	 * Add various elements for the OpenSearch protocol to work
	 *
	 * @param string $xml XML generated so far by the AtomHandler::get_collection() method
	 * @param array $params Parameters used to fetch results, will be used to find total results and where to start (index)
	 * @return string XML OpenSearch results Atom feed
	 */
	public function filter_atom_get_collection( $xml, $params ) {
		$criteria= $params['criteria'];
		
		$totalResults= Posts::get( $params )->count_all();
		$startIndex= isset( $params['page'] ) ? $params['page'] : 1;
		$itemsPerPage= isset( $this->handler_vars['count'] ) ? $this->handler_vars['count'] : Options::get( 'pagination' );
		
		$xml->addChild( 'opensearch:totalResults', $totalResults);
		$xml->addChild( 'opensearch:startIndex', $startIndex );
		$xml->addChild( 'opensearch:itemsPerPage', $itemsPerPage );
		$xml_os_query= $xml->addChild( 'opensearch:Query' );
		$xml_os_query->addAttribute( 'role', 'request' );
		$xml_os_query->addAttribute( 'searchTerms', $criteria );
		$xml_os_query->addAttribute( 'startPage', 1 );
		
		return $xml;
	}
	
	/**
	 * Act function called by the `Controller` class.
	 * Dispatches the request to the proper action handling function.
	 *
	 * @param string $action Action requested, either Search or osDescription
	 */
	public function act( $action )
	{
		self::$action();
	}
	
	/**
	 * Output the OpenSearch description file
	 *
	 * @return string XML OpenSearch description file
	 */
	public function osDescription() {
		$template_url= Site::get_url( 'habari' ) . '/search/{searchTerms}/page/{startPage}/count/{count}/atom';
		
		$xml= '<?xml version="1.0" encoding="UTF-8"?><OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"></OpenSearchDescription>';

		$xml= new SimpleXMLElement( $xml );
		$xml_sn= $xml->addChild( 'ShortName', Options::get('title') );
		$xml_description= $xml->addChild( 'Description', Options::get('tagline') );
		$xml_tags= '';
		$xml_contact= '';
		$xml_url= $xml->addChild( 'Url' );
		$xml_url->addAttribute( 'type', 'text/html');
		$xml_url->addAttribute( 'template', $template_url );
		
		$xml= $xml->asXML();
 
		/* Clean the output buffer, so we can output from the header/scratch. */
		ob_clean();
		header( 'Content-Type: application/opensearchdescription+xml' );
		print $xml;
	}
	
	/**
	 * Searches content based on criteria
	 * Creates a new Atom feed based on criteria and other parameters (pagination, limit, etc.)
	 */
	public function search() {
		$criteria= $this->handler_vars['criteria'];
		$page= isset( $this->handler_vars['page'] ) ? $this->handler_vars['page'] : 1;
		$limit= isset( $this->handler_vars['count'] ) ? $this->handler_vars['count'] : Options::get( 'pagination' );
		
		preg_match_all( '/(?<=")(\\w[^"]*)(?=")|(\\w+)/', $criteria, $matches );
		$words= $matches[0];
		
		$where= 'status = ?';
		$params= array( Post::status( 'published' ) );
		foreach ( $words as $word ) {
			$where .= " AND (title LIKE CONCAT('%',?,'%') OR content LIKE CONCAT('%',?,'%'))";
			$params[] = $word;
			$params[] = $word;  // Not a typo
		}

		$user_filters= array( 'where' => $where, 'params' => $params, 'page' => $page, 'criteria' => $criteria, 'limit' => $limit );
		$atomhandler= new AtomHandler();
		$atomhandler->handler_vars['index']= $page;
		$atomhandler->get_collection( $user_filters );
	}
	
	/**
	 * Add the link and meta data to the header
	 *
	 * @param object $theme Theme object with all its properties and methods available
	 */
	public function theme_header( $theme ) {
		$search_url= Site::get_url('habari') . '/opensearch.xml';
		$site_title= Options::get('title');
		
		echo '<link rel="search" type="application/opensearchdescription+xml" href="' . $search_url . '" title="' . $site_title . '">'."\r\n";

		if ( Controller::get_action() == 'search' ) {
			$totalResults= $theme->posts->count_all();
			$startIndex= $theme->page;
			$itemsPerPage= isset( $this->handler_vars['count'] ) ? $this->handler_vars['count'] : Options::get( 'pagination' );
			echo '<meta name="totalResults" content="' . $totalResults . '">'."\r\n";
			echo '<meta name="startIndex" content="' . $startIndex . '">'."\r\n";
			echo '<meta name="itemsPerPage" content="' . $itemsPerPage . '">'."\r\n";
		}
	}

}
?>
