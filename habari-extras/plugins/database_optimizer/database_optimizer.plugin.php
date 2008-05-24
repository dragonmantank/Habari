<?php

	class DatabaseOptimizer extends Plugin
	{
		
		const VERSION= '0.3.1';
		
		public function info ( ) {
			
			return array (
					'name' => 'Database Optimizer',
					'url' => 'http://habariproject.org',
					'author' => 'Habari Community',
					'authorurl' => 'http://habariproject.org',
					'version' => self::VERSION,
					'description' => 'Automagically optimizes your database tables weekly.',
					'license' => 'Apache License 2.0'
			);
			
		}
		
			
		public function action_update_check ( ) {

			Update::add( 'DatabaseOptimizer', 'E619A6D0-15F8-11DD-8567-98DE55D89593', self::VERSION );
		
		}
		
		public function action_plugin_activation ( $file='' ) {
			
			if ( $file == $this->get_file() ) {
				
				// add our cronjob to kick off weekly henceforth
				CronTab::add_weekly_cron( 'optimize database tables', 'optimize_database', 'Optimizes database tables automagically.' );
				EventLog::log( 'CronTab added to optimize database tables weekly.' );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file='' ) {
			
			if ( $file == $this->get_file() ) {
				
				CronTab::delete_cronjob( 'optimize database tables' );
				
			}
			
		}
		
		public function filter_optimize_database ( $result, $paramarray ) {
						
			$space_saved = 0;
			$tables = 0;
			
			switch ( DB::get_driver_name() ) {
				
				case 'mysql':
					
					$q= 'SHOW TABLE STATUS WHERE data_free > 0';
					
					$tables= DB::get_results( $q );
					
					if ( count( $tables ) > 0 ) {
						
						foreach ( $tables as $table ) {
							
							$q2= 'OPTIMIZE TABLE ' . $table->Name;
							
							if ( DB::query( $q2 ) ) {
								$space_saved += $table->Data_free;
								$tables++;
							}
							
						}
						
						EventLog::log( 'Database Tables Optimized. ' . Utils::human_size( $space_saved ) . ' reclaimed from ' . Locale::_n( 'table', 'tables', $tables ) . '.' );
						
					}
					
					$result= true;
					break;
					
				case 'sqlite':
					
					if ( DB::query( 'VACUUM' ) ) {
						$result= true;
						
						EventLog::log( 'SQLite database VACUUM\'ed successfully.' );
					}
					else {
						$result= false;
					}
					
					break;
					
				default:
					$result= false;
					break;
					
				return $result;
				
			}
			
		}
		
		
	}

?>
