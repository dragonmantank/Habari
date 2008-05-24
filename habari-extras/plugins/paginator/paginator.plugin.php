<?php
/**
 * Habari Paginator plugin, a more powerful pagination.
 * 
 * @package Habari
 */

class Paginator extends Plugin {

	/**
	 * Provide plugin info to the system
	 */	 	
	public function info() {
		return array(
			'name' => 'Paginator',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Decide how the pagination, either as a `unordered list` or as a `span`.',
			'copyright' => '2007'
		);
	}
 
	/**
	 * Formats the pagination array.
	 * @param array Pagination array
	 * @param string Style to return [ list | span ]
	 * @return string Formatted page navigation
	 */
	public function format( $pagination, $style )
	{
		switch ( $style ) {
			case 'list':
				$out= '<ul class="page_selector">';
				foreach ( $pagination['pages'] as $page ) {
					if ( is_array( $page ) ) {
						$out.= '<li><a href="' . $page['url'] . '"' . ( ( $page['caption'] == $pagination['current'] ) ? ' class="current"'  : '' ) . '>' . $page['caption'] . '</a></li>';
					} else {
						$out.= '<li>' . $page . '</li>';
					}
				}
				$out.= '</ul>';
				break;
			case 'span':
				$out= '<span class="page_selector">';
				foreach ( $pagination['pages'] as $page ) {
					if ( is_array( $page ) ) {
					 $out.= '<a href="' . $page['url'] . '"' . ( ( $page['caption'] == $pagination['current'] ) ? ' class="current">[' . $page['caption'] . ']' : '>' . $page['caption'] ) . '</a>';
					}
					else {
						$out.= $page;
					}
				}
				$out.= '</span>';
				break;
		}
		
		return $out;
	}
	
 	/**
 	 * function page_selector
	 * Returns a page selector
	 *
	 * The $paramarray can contain:
	 * 'current' => Current page
	 * 'total' => Total pages
	 * 'token' => Token for the URL calls
	 * 'settings' => Array of settings for the URL calls
	 *
	 * @param array parameters to render the pagination
	 * @return array contains a 'current' and 'pages' key
 	 **/
	public function get( $current, $total, $rr_name= NULL, $settings = array() )
 	{
 		// Extract the style and remove it from the array.
 		if ( isset( $settings['style'] ) ) {
 			$style= $settings['style'];
 			unset( $settings['style'] );
 		}
 		else {
 			$style= 'span';
 		}
 		
		// If RewriteRule name is not supplied, use the current RewriteRule
		if ( $rr_name == '' ) {
			$rr= URL::get_matched_rule();
		}
		else {
			list( $rr )= RewriteRules::by_name( $rr_name );
		}
		
		// Retrieve the RewriteRule and aggregate an array of matching arguments
		$rr_named_args= $rr->named_args;
		$rr_args= array_merge( $rr_named_args['required'], $rr_named_args['optional']  );
		$rr_args_values= array();
		foreach ( $rr_args as $rr_arg ) {
			$rr_arg_value= Controller::get_var( $rr_arg );
			if ( $rr_arg_value != '' ) {
				$rr_args_values[$rr_arg]= $rr_arg_value;
			}
		}

		$settings= array_merge( $settings, $rr_args_values );

		// Current page
		if ( $current > $total ) {
			$current= $total;
 		}

		$p= array( 'current' => $current );
		
		// 1 - First page
		$p['pages'][1]['caption']= 1;

		// Skip if there is only one page
		if ( $total > 1 ) {
			// &amp;
			if ( ( $current != 1 || $current != $total ) && ( $current - 2 > 1 ) ) {
				$p['pages'][]= '&hellip;';
 			}
		
			// Previous Page
			if ( $current - 1 > 1 ) {
				$p['pages'][]['caption']= $current - 1;
 			}
			
			// Current Page
			if ( $current > 1 && $current < $total ) {
				$p['pages'][]['caption']= $current;
			}
			
			// Next Page
			if ( $current + 1 < $total ) {
				$p['pages'][]['caption']= $current + 1;
			}
			
			// &hellip;
			if ( ( $current != 1 || $current != $total ) && ( $current + 2 < $total ) ) {
				$p['pages'][]= '&hellip;';
			}
			
			// Last page
			$p['pages'][]['caption']= $total;
 		}
 
		$count= count( $p['pages'] );
		for($z= 1; $z <= $count; $z++) {
			if ( is_array( $p['pages'][$z] ) ) {
				$p['pages'][$z]['url']= $rr->build( array_merge($settings, array('page'=>$p['pages'][$z]['caption'])), false);
			}
		}
 		
 		return self::format( $p, $style );
 	}
 	
 	public function out( $current, $total, $rr_name= NULL, $settings = array() ) {
 		print self::get( $current, $total, $rr_name, $settings );
 	}

}
?>
