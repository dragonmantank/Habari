<?php

class CustomQuery extends Plugin
{
	const VERSION= '0.1';
	
	public function info()
	{
		return array( 
			'name' => 'Custom Query',
			'author' => 'Habari Community',
			'description' => 'Allows you to set the number of post to display for each page.',
			'url' => 'http://habariproject.org',
			'version' => self::VERSION,
			'license' => 'Apache License 2.0'
			);
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
      {
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure', 'customquery');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure', 'customquery') :
					$ui = new FormUI( 'customquery' );
					
					foreach ( RewriteRules::get_active()->getArrayCopy() as $rule ) {
						if ( strpos( $rule->name, 'display_' ) === 0 ) {
							$ui->add( 'text', $rule->name, 'Number of Posts for ' . $rule->name );
						}
					}
					
					$ui->out();
					break;
			}
		}
	}
	
	public function filter_template_where_filters( $where_filters )
	{
		if ( Options::get( 'customquery:' . URL::get_matched_rule()->name ) ) {
			$where_filters['limit']= Options::get( 'customquery:' . URL::get_matched_rule()->name );
		}
		return $where_filters;
	}
}

?>
