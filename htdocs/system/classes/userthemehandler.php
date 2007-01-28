<?php
/**
 * Class for handling default user theme actions.
 * 
 * @note Any theme will be able to override these default actions by registering an override function with Theme->act_some_function()
 */
class UserThemeHandler extends ActionHandler
{
	private $theme= null;

	/**
	 * Constructor for the default theme handler.  Here, we 
	 * automatically load the active theme for the installation, 
	 * and create a new Theme instance.
	 */
	public function __construct()
	{
		$this->theme= Themes::create();
	}

	/**
	 * The UserThemeHandler's act() method differs from ActionHandler's
	 * act() method in one distinct way: if the Handler's theme variable
	 * registers an override action via Theme->register_action(), then
	 * that function is called instead of the default handler action. 
	 *
	 * @param   action  the action that was in the URL rule
	 * @return  bool    did the action succeed?
	 */	 	 	 	 
	public function act( $action )
	{
		$this->action= $action;
		
		$action_method= 'act_' . $action;
		$before_action_method= 'before_' . $action_method;
		$after_action_method= 'after_' . $action_method;
		
		if ( method_exists( $this->theme, $action_method ) ) {
			if ( method_exists( $this->theme, $before_action_method ) ) {
				$this->theme->$before_action_method();
			}
			$this->theme->$action_method();
			if ( method_exists( $this->theme, $after_action_method ) ) {
				$this->theme->$after_action_method();
			}
		}
		else {
			/* OK, theme didn't override, so use the default handler */
			 if ( method_exists( $this, $action_method ) ) {
				if ( method_exists( $this, $before_action_method ) ) {
					$this->$before_action_method();
				}
				$this->$action_method();
				if ( method_exists( $this, $after_action_method ) ) {
					$this->$after_action_method();
				}
			}
		}
	}

	/**
	 * Grabs post data and inserts that data into the internal
	 * handler_vars array, which eventually gets extracted into 
	 * the theme's ( and thereby the template_engine's ) local
	 * symbol table for use in the theme's templates
	 * 
	 * This is the default, generic function to grab posts.  To 
	 * "filter" the posts retrieved, simply pass any filters to 
	 * the handler_vars variables associated with the post retrieval.
	 * For instance, to filter by tag, ensure that handler_vars['tag']
	 * contains the tag to filter by.  Simple as that.
	 */
	public function act_display_posts()
	{
		/* 
		 * We build the Post filters by analyzing the handler_var
		 * data which is assigned to the handler ( by the Controller and 
		 * also, optionally, by the Theme )
		 */
		$valid_filters= array( 
			  'contenttype'
			, 'slug'
			, 'status'
			, 'page' // pagination
			, 'tag'
			, 'month'
			, 'year'
			, 'day'
		);
		$where_filters= array();
		$where_filters = array_intersect_key( $this->handler_vars, array_flip( $valid_filters ) );

		$posts= Posts::get( $where_filters );
		if ( count( $posts ) == 1 && count( $where_filters ) > 0 ) {
			$this->handler_vars['post']= $posts[0];
			$template= 'post';
		}
		else {
			// Automatically assigned to theme at display time.
			$this->handler_vars['posts']= $posts;
			$template= 'posts';
		}
		$this->display( $template );
		return true;
	}

	/**
	 * Helper function which automatically assigns all handler_vars
	 * into the theme and displays a theme template
	 * 
	 * @param template_name Name of template to display ( note: not the filename )
	 */
	protected function display( $template_name )
	{
		/* 
		 * Assign internal variables into the theme ( and therefore into the theme's template
		 * engine.  See Theme::assign().
		 */
		foreach ( $this->handler_vars as $key => $value ) {
			$this->theme->assign( $key, $value );
		}
		$this->theme->display( $template_name );
	}
}

?>
