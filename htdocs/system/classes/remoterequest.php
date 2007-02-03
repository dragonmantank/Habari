<?php

interface RequestProcessor
{
	public function execute( $method, $url, $headers, $body, $timeout );

	public function get_response_body();
	public function get_response_headers();
}

/**
 * Generic class to make outgoing HTTP requests.
 */
class RemoteRequest
{
	private $method= 'GET';
	private $url;
	private $params= array();
	private $headers= array();
	private $body= '';
	private $timeout= 180;
	private $processor= NULL;
	
	private $response_body= '';
	private $response_headers= '';
	
	private $user_agent= 'Habari'; // TODO add version to that (Habari/0.1.4) 
	
	/**
	 * @param string $url URL to request
	 * @param string $method Request method to use (default 'GET')
	 * @param int $timeuot Timeout in seconds (default 180)
	 */
	public function __construct( $url, $method= 'GET', $timeout= 180 )
	{
		$this->method= strtoupper( $method );
		$this->url= $url;
		$this->timeout= $timeout;
		
		$this->add_header( array( 'User-Agent' => $this->user_agent ) );
		
		// can't use curl's followlocation in safe_mode with open_basedir, so
		// fallback to srp for now
		if ( function_exists( 'curl_init' )
			 && ! ( ini_get( 'safe_mode' ) && ini_get( 'open_basedir' ) ) ) {
			$this->processor= new CURLRequestProcessor;
		}
		else {
			$this->processor= new SocketRequestProcessor;
		}
	}
	
	/**
	 * DO NOT USE THIS FUNCTION.
	 */
	public function __set_processor( $processor )
	{
		$this->processor= $processor;
	}
	
	/**
	 * Add a request header.
	 * @param mixed $header The header to add, either as a string 'Name: Value' or an associative array 'name'=>'value'
	 */
	public function add_header( $header )
	{
		if ( is_array( $header ) ) {
			$this->headers= array_merge( $this->headers, $header );
		}
		else {
			list( $k, $v )= explode( ': ', $header );
			$this->headers[$k]= $v;
		}
	}
	
	/**
	 * Add a list of headers.
	 * @param array $headers List of headers to add.
	 */
	public function add_headers( $headers )
	{
		foreach ( $headers as $header ) {
			$this->add_header( $header );
		}
	}
	
	/**
	 * Set the request body.
	 * Only used with POST requests, will raise a warning if used with GET.
	 * @param string $body The request body.
	 */
	public function set_body( $body )
	{
		if ( $this->method !== 'POST' )
			return Error::raise( _t('Trying to add a request body to a non-POST request'), E_USER_WARNING );
		
		$this->body= $body;
	}
	
	/**
	 * Set the request query parameters (i.e., the URI's query string).
	 * Will be merged with existing query info from the URL.
	 * @param array $params
	 */
	public function set_params( $params )
	{
		if ( ! is_array( $params ) )
			$params= parse_str( $params );
		
		$this->params= $params;
	}
	
	/**
	 * A little housekeeping.
	 */
	private function prepare()
	{
		// remove anchors (#foo) from the URL
		$this->url= $this->strip_anchors( $this->url );
		// merge query params from the URL with params given
		$this->url= $this->merge_query_params( $this->url, $this->params );
		
		if ( $this->method === 'POST' ) {
			$this->add_header( array( 'Content-Length' => strlen( $this->body ) ) );
			if ( ! isset( $this->headers['Content-Type'] ) ) {
				// TODO should raise a warning
				$this->add_header( array( 'Content-Type' => 'application/x-www-form-urlencoded' ) );
			}
		}
	}
	
	/**
	 * Actually execute the request.
	 * On success, returns TRUE and populates the response_body and response_headers fields.
	 * On failure, throws error.
	 */
	public function execute()
	{
		$this->prepare();
		$result= $this->processor->execute( $this->method, $this->url, $this->headers, $this->body, $this->timeout );
		if ( $result ) {
			$this->response_headers= $this->processor->get_response_headers();
			$this->response_body= $this->processor->get_response_body();
			$this->executed= TRUE;
			
			return TRUE;
		}
		else {
			// actually, processor->execute should throw an Error which would bubble up
			// we need a new Error class and error handler for that, though
			
			return $result;
		}
	}
	
	/**
	 * Return the response headers. Raises a warning and returns '' if the request wasn't executed yet.
	 */
	public function get_response_headers()
	{
		if ( !$this->executed )
			return Error::raise( _t('Trying to fetch response headers for a pending request.'), E_USER_WARNING );
		
		return $this->response_headers;
	}
	
	/**
	 * Return the response body. Raises a warning and returns '' if the request wasn't executed yet.
	 */
	public function get_response_body()
	{
		if ( !$this->executed )
			return Error::raise( _t('Trying to fetch response body for a pending request.'), E_USER_WARNING );
		
		return $this->response_body;
	}
	
	/**
	 * Remove anchors (#foo) from given URL.
	 */
	private function strip_anchors( $url )
	{
		return preg_replace( '/(#.*?)?$/', '', $url );
	}
	
	/**
	 * Call the filter hook.
	 */
	private function __filter( $data, $url )
	{
		return Plugins::filter( 'remoterequest', $data, $url );
	}
	
	/**
	 * Merge query params from the URL with given params.
	 * @param string $url The URL
	 * @param string $params An associative array of parameters.
	 */
	private function merge_query_params( $url, $params )
	{
		$urlparts= parse_url( $url );
		
		if ( ! isset( $urlparts['query'] ) )
			$urlparts['query']= '';
		
		if ( ! is_array( $params ) )
			parse_str( $params, $params );
		
		$urlparts['query']= http_build_query( array_merge( Utils::get_params( $urlparts['query'] ), $params ), '', '&' );
		
		return Utils::glue_url( $urlparts );
	}
}

?>