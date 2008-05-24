<?php

/*
 * Monthly Archives Plugin
 * Usage: <?php echo $theme->monthly_archives(); ?>
 * 
 * Modified / fixed from the original by Raman Ng (a.k.a. tinyau): http://blog.tinyau.net
 * 		http://blog.tinyau.net/archives/2007/09/22/habari-rn-monthly-archives-plugin
 */
class MonthlyArchives extends Plugin
{
	const VERSION= '0.8.1';

	private $config= array();
	private $monthly_archives= ''; // string stored archives to be displayed

	public function info()
	{
		return array(
			'name' => 'Monthly Archives',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'Shows archives grouped by month.',
			'license' => 'Apache License 2.0'
		);
	}
	
	public function action_plugin_deactivation ( $file='' ) {
		
		if ( $file == $this->get_file() ) {
			
			$class_name = strtolower( get_class( $this ) );
			
			// clean up all our options - we're being deactivated!
			// @todo Uncomment when we finally get Options::delete() available!
			/*
			Options::delete( $class_name . ':num_month' );
			Options::delete( $class_name . ':display_month' );
			Options::delete( $class_name . ':show_count' );
			Options::delete( $class_name . ':detail_view' );
			Options::delete( $class_name . ':delimiter' );
			*/
			
		}
		
	}

	public function action_init()
	{
		$class_name= strtolower( get_class( $this ) );
		$this->config['num_month']= Options::get( $class_name . ':num_month' );
		$this->config['display_month']= Options::get( $class_name . ':display_month' );
		$this->config['show_count']= Options::get( $class_name . ':show_count' );
		$this->config['detail_view']= Options::get( $class_name . ':detail_view' );
		$this->config['delimiter']= Options::get( $class_name . ':delimiter' );
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure' );
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$class_name= strtolower( get_class( $this ) );
					$ui= new FormUI( $class_name );
					$num_month= $ui->add( 'text', 'num_month', _t( 'Number of most recent months to be shown (Blank for show all)' ) );

					$display_month= $ui->add( 'select', 'display_month', _t( 'Month displayed as' ) );

					$display_month->options= array( '' => '', 'F' => 'Full name', 'A' => 'Abbreviation', 'N' => 'Number' );

					$show_count= $ui->add( 'select', 'show_count', _t( 'Show Monthly Entries Count?' ) );
					$show_count->options= array( '' => '', 'Y' => 'Yes', 'N' => 'No' );
					$show_count->add_validator( 'validate_required' );

					$detail_view= $ui->add( 'select', 'detail_view', _t( 'Detail View?' ) );
					$detail_view->options= array( '' => '', 'Y' => 'Yes', 'N' => 'No' );
					$detail_view->add_validator( 'validate_detail_view' );

					$delimiter= $ui->add( 'text', 'delimiter', _t( 'Delimiter to separate day and post title in detail view (optional)' ) );
					$delimiter->add_validator( 'validate_delimiter' );

					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					break;
			}
		}
	}
	
	public function updated_config( $ui )
	{
		return true;  
	}
	
	public function theme_monthly_archives ( $theme ) {
		
		if ( !empty( $this->config['display_month'] ) && !empty( $this->config['show_count'] ) && !empty( $this->config['detail_view'] ) ) {
			
			$this->get_monthly_archives();
			
		}
		
		if ( !empty( $this->monthly_archives ) ) {
			$theme->monthly_archives= $this->monthly_archives;
		}
		else {
			$theme->monthly_archives= '<ul><li>Plugin not yet configured</li></ul>';
		}
		
		return $theme->monthly_archives;
		
	}

	public function filter_validate_detail_view( $valid, $value )
	{
		if ( empty( $value ) || $value == '' ) {
			return array( _t( 'A value for this field is required.' ) );
		}
		$this->config['detail_view']= $value;
		return array();
	}

	public function filter_validate_delimiter( $valid, $value )
	{
		if ( 'N' == $this->config['detail_view'] ) {
			if ( !empty( $value ) ) {
				return array( _t( 'No value should be provided for summary view.' ) );
			}
		}
		return array();
	}

	private function get_monthly_archives()
	{
		$post_type= Post::type( 'entry' );
		$post_status= Post::status( 'published' );
		$now= date( 'Y-m-d H:i:s' );

		$limit= ( empty( $this->config['num_month'] ) ? '' : "LIMIT {$this->config['num_month']}" );
		$sql= "
			SELECT YEAR(p.pubdate) AS year, MONTH(p.pubdate) AS month,
			COUNT(p.id) AS cnt
			FROM " . DB::table( 'posts' ) . " p
			WHERE p.content_type = {$post_type}
			AND p.status = {$post_status}
			AND p.pubdate < '{$now}'
			GROUP by year, month
			ORDER BY p.pubdate DESC
			{$limit}";
		$yr_mths= DB::get_results( $sql );

		$this->monthly_archives.= "<ul class=\"archive-month\">\n";
		foreach ( $yr_mths as $yr_mth ) {
			$month= substr( '0' . $yr_mth->month, -2, 2 );
			switch ( $this->config['display_month'] ) {
				case 'F': // Full name
					$display_month= date( 'F', mktime( 0, 0, 0, $yr_mth->month ) );
					break;
				case 'A': // Abbreviation
					$display_month= date( 'M', mktime( 0, 0, 0, $yr_mth->month ) );
					break;
				case 'N': // Number
					$display_month= $month;
					break;
			}
			$this->monthly_archives.= '<li><a href="' . URL::get( 'display_entries_by_date', array( 'year' => $yr_mth->year, 'month' => $month ) ) . '" title="View entries in ' . "{$display_month} {$yr_mth->year}". '">' . "{$display_month} {$yr_mth->year}</a>";
			if ( 'Y' == $this->config['show_count'] ) {
				$this->monthly_archives.= " ({$yr_mth->cnt})";
			}

			if ( 'N' == $this->config['detail_view'] ) {
				$this->monthly_archives.= "</li>\n";
			}
			elseif ( 'Y' == $this->config['detail_view'] ) {
				// Show post title as well
				$posts= Posts::get( array(
					'content_type' => $post_type,
					'status' => $post_status,
					'year' => $yr_mth->year,
					'month' => $yr_mth->month,
					'nolimit' => 1,
					) );
				if ( $posts ) {
					$this->monthly_archives.= "\n<ul class=\"archive-entry\">\n";
					foreach ( $posts as $post ) {
						$day= date( 'd', strtotime( $post->pubdate ) );

						$this->monthly_archives.= "<li>{$day}";
						$this->monthly_archives.= ( empty( $this->config['delimiter'] ) ? "&nbsp;&nbsp;" : htmlspecialchars( $this->config['delimiter'] ) );
						$this->monthly_archives.= "<a href=\"{$post->permalink}\">{$post->title}</a></li>\n";
					}
					$this->monthly_archives.= "</ul>\n";
				}
				$this->monthly_archives.= "</li>\n";
			}
		}
		$this->monthly_archives.= "</ul>\n";
	}
}
?>
