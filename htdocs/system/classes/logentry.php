<?php
/**
 * @package Habari
 *
 */

/**
 * Habari LogEntry class
 *
 * Represents a log entry
 *
 * @todo Apply system error handling
 */
class LogEntry extends QueryRecord
{

	/**
	 * Defined event severities
	 *
	 * @final
	 */
	private static $severities = array(
		'any',
		'none', // should not be used
		'debug', 'info', 'notice', 'warning', 'err', 'crit', 'alert', 'emerg',
	);

	/**
	 * Cache for log_types
	 */
	private static $types = array();

	/**
	 * Return the defined database columns for an Event
	 *
	 * @return array Array of columns in the LogEntry table
	 */
	public static function default_fields()
	{
		return array(
			'id' => 0,
			'user_id' => 0,
			'type_id' => NULL,
			'severity_id' => NULL,
			'message' => '',
			'data' => '',
			'timestamp' => HabariDateTime::date_create(),
			'ip' => 0,
		);
	}

	/**
	 * Constructor for the LogEntry class
	 *
	 * @param array $paramarray an associative array of initial LogEntry field values
	 */
	public function __construct( $paramarray = array() )
	{
		// Defaults
		$this->fields = array_merge(
			self::default_fields(),
			$this->fields );

		parent::__construct( $paramarray );
		if ( !isset( $this->fields['module'] ) ) {
			$this->fields['module'] = 'habari';
		}
		if ( !isset( $this->fields['type'] ) ) {
			$this->fields['type'] = 'default';
		}
		if ( !isset( $this->fields['severity'] ) ) {
			$this->fields['severity'] = 'info';
		}
		if ( isset( $this->fields['timestamp'] ) ) {
			$this->fields['timestamp'] = HabariDateTime::date_create( $this->fields['timestamp'] );
		}
		$this->exclude_fields( 'id' );
	}

	/**
	 * Returns an associative array of LogEntry types
	 *
	 * @param bool whether to force a refresh of the cached values
	 * @return array An array of log entry type names => integer values
	 */
	public static function list_logentry_types( $force = false )
	{
		if ( $force || empty( self::$types ) ) {
			self::$types = array();
			$res = DB::get_results( 'SELECT id, module, type FROM {log_types}' );
			foreach ( $res as $x ) {
				self::$types[ $x->module ][ $x->type ] = $x->id;
			}
		}
		return self::$types;
	}

	/**
	 * Return an array of Severities
	 * @return array An array of severity ID => name pairs
	**/
	public static function list_severities()
	{
		foreach ( self::$severities as $id => $name ) {
			if ( 'none' == $name ) {
				continue;
			}
			$results[$id] = $name;
		}
		return $results;
	}

	/**
	 * Returns an array of LogEntry modules
	 * @param bool Whether to refresh the cached values
	 * @return array An array of LogEntry module id => name pairs
	**/
	public static function list_modules( $refresh = false )
	{
		$types = self::list_logentry_types( $refresh );
		foreach ( $types as $module => $types ) {
			$modules[] = $module;
		}
	}

	/**
	 * Returns an array of LogEntry types
	 * @param bool Whether to refresh the cached values
	 * @return array An array of LogEntry id => name pairs
	**/
	public static function list_types( $refresh = false )
	{
		$types = array();
		$matrix = self::list_logentry_types( $refresh );
		foreach ( $matrix as $module => $module_types ) {
			$types = array_merge( $types, $module_types );
		}
		return array_flip( $types );
	}

	/**
	 * Get the integer value for the given severity, or <code>false</code>.
	 *
	 * @param string $severity The severity name
	 * @return mixed numeric value for the given severity, or <code>false</code>
	 */
	public static function severity( $severity )
	{
		if ( is_numeric( $severity ) && array_key_exists( $severity, self::$severities ) ) {
			return $severity;
		}
		return array_search( $severity, self::$severities );
	}

	/**
	 * Get the string representation of the severity numeric value.
	 *
	 * @param integer $severity The severity index.
	 * @return string The string name of the severity, or 'Unknown'.
	 */
	public static function severity_name( $severity )
	{
		return isset( self::$severities[$severity] ) ? self::$severities[$severity] : _t( 'Unknown' );
	}

	/**
	 * Get the integer value for the given module/type, or <code>false</code>.
	 *
	 * @param string $module the module
	 * @param string $type the type
	 * @return mixed numeric value for the given module/type, or <code>false</code>
	 */
	public static function type( $module, $type )
	{
		self::list_logentry_types();
		if ( array_key_exists( $module, self::$types ) && array_key_exists( $type, self::$types[$module] ) ) {
			return self::$types[$module][$type];
		}
		if ( $type != null && $module != null ) {
			EventLog::register_type( $type, $module );
			self::list_logentry_types( true );
			return self::$types[$module][$type];
		}
		return false;
	}

	/**
	 * Insert this LogEntry data into the database
	 */
	public function insert()
	{
		if ( isset( $this->fields['severity'] ) ) {
			$this->severity_id = LogEntry::severity( $this->fields['severity'] );
			unset( $this->fields['severity'] );
		}
		if ( isset( $this->fields['module'] ) && isset( $this->fields['type'] ) ) {
			$this->type_id = LogEntry::type( $this->fields['module'], $this->fields['type'] );
			unset( $this->fields['module'] );
			unset( $this->fields['type'] );
		}

		Plugins::filter( 'insert_logentry', $this );
		parent::insertRecord( DB::table( 'log' ) );
		
		$this->id = DB::last_insert_id();
		
	}

	/**
	 * Return a single requested log entry.
	 *
	 * <code>
	 * $log= LogEntry::get( array( 'id' => 5 ) );
	 * </code>
	 *
	 * @param array $paramarray An associated array of parameters, or a querystring
	 * @return object LogEntry The first log entry that matched the given criteria
	 */
	public static function get( $paramarray = array() )
	{
		// Default parameters.
		$defaults = array (
			'fetch_fn' => 'get_row',
		);
		$user = User::identify();
		if ( $user->loggedin ) {
			$defaults['where'][] = array(
				'user_id' => $user->id,
			);
		}
		foreach ( $defaults['where'] as $index => $where ) {
			$defaults['where'][$index] = array_merge( $where, Utils::get_params( $paramarray ) );
		}
		// Make sure we fetch only a single event. (LIMIT 1)
		$defaults['limit'] = 1;

		return EventLog::get( $defaults );
	}

	/**
	 * Return the log entry's event type.
	 *
	 * <code>$log->type</code>
	 *
	 * @return string Human-readable event type
	 */
	public function get_event_type() {
		$type = DB::get_value( 'SELECT type FROM {log_types} WHERE id=' . $this->type_id );
		return $type ? $type : _t( 'Unknown' );
	}

	/**
	 * Return the log entry's event module.
	 *
	 * <code>$log->module</code>
	 *
	 * @return string Human-readable event module
	 */
	public function get_event_module() {
		$module = DB::get_value( 'SELECT module FROM {log_types} WHERE id=' . $this->type_id );
		return $module ? $module : _t( 'Unknown' );
	}

	/**
	 * Return the log entry's event severity.
	 *
	 * <code>$log->severity</code>
	 *
	 * @return string Human-readable event severity
	 */
	public function get_event_severity() {
		return self::severity_name( $this->severity_id );
	}

	/**
	 * function delete
	 * Deletes this logentry
	 */
	public function delete()
	{
		$allow = true;
		$allow = Plugins::filter( 'logentry_delete_allow', $allow, $this );
		if ( ! $allow ) {
			return;
		}
		Plugins::act( 'logentry_delete_before', $this );
		return parent::deleteRecord( DB::table( 'log' ), array( 'id'=>$this->id ) );
		Plugins::act( 'logentry_delete_after', $this );
	}

	/**
	 * Overrides QueryRecord __get to implement custom object properties
	 *
	 * @param string Name of property to return
	 * @return mixed The requested field value
	 */
	public function __get( $name )
	{
		$fieldnames = array_merge( array_keys( $this->fields ), array( 'module', 'type', 'severity' ) );
		if ( !in_array( $name, $fieldnames ) && strpos( $name, '_' ) !== false ) {
			preg_match( '/^(.*)_([^_]+)$/', $name, $matches );
			list( $junk, $name, $filter )= $matches;
		}
		else {
			$filter = false;
		}

		switch ( $name ) {
		case 'module':
			$out = $this->get_event_module();
			break;
		case 'type':
			$out = $this->get_event_type();
			break;
		case 'severity':
			$out = $this->get_event_severity();
			break;
		default:
			$out = parent::__get( $name );
			break;
		}
		$out = Plugins::filter( "logentry_{$name}", $out, $this );
		if ( $filter ) {
			$out = Plugins::filter( "logentry_{$name}_{$filter}", $out, $this );
		}
		return $out;
	}

	/**
	 * Overrides QueryRecord __set to implement custom object properties
	 *
	 * @param string Name of property to return
	 * @return mixed The requested field value
	 */
	public function __set( $name, $value )
	{
		switch ( $name ) {
		case 'timestamp':
			if ( !($value instanceOf HabariDateTime) ) {
				$value = HabariDateTime::date_create($value);
			}
			break;
		}
		return parent::__set( $name, $value );
	}

}

?>
