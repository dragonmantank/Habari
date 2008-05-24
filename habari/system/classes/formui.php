<?php
/**
 * FormUI Library - Create interfaces for plugins
 *
 * FormUI			This is the main class, it generates the form itself;
 * FormContainer	A form-related class that can contain form elements, derived by FormUI and FormControlFieldset;
 * FormValidators	Catalog of validation functions, it can be extended if needed;
 * FormControl		Parent class to controls, it contains basic functionalities overrode in each control's class;
 * FormControl*		Every control needs a FormControl* class, FormUI literally looks for example, FormControlCheckbox.
 *
 * @version $Id$
 * @package Habari
 */

class FormContainer
{
	protected $name= '';
	public $controls= array();
	protected $theme_obj = null;

	/**
	 * Constructor for FormContainer prevents construction of this class directly
	 */
	private function __construct() {}

	/**
	 * Add a control to this form.
	 * If a default value is not provided, the function attempts to obtain the value
	 * from the Options table using the form name and the control name as the key.  For
	 * example, if the form name is "myform" and the control name is "mycontrol" then
	 * the function attempts to obtain the control's value from Options::get('myform:mycontrol')
	 * These settings may also be used in FormUI::success() later to write the value
	 * of the control back into the options table.
	 *
	 * @param string $type A classname, or the postfix of a class starting 'FormControl' that will be used to create the control
	 * @param string $name The name of the control, also the latter part of the Options table key value
	 * @return FormControl An instance of the named FormControl descendant.
	 */
	public function add($type, $name)
	{
		$control= null;
		$args= func_get_args();
		array_shift($args); // Remove $type, keep name for override __construct()


		if(is_string($type) && class_exists('FormControl' . ucwords($type))) {
			$type= 'FormControl' . ucwords($type);
		}
		if(strpos($name, 'user:') === 0) {
			$store_user = true;
			$name = substr($name, 5);
			$storage_name = $this->name . '_' . $name;
		}
		else {
			$store_user = false;
			$storage_name = $this->name . ':' . $name;
		}

		if(class_exists($type)) {
			// Instanciate a new object from $type
			$controlreflect= new ReflectionClass($type);
			$control= $controlreflect->newInstanceArgs($args);
			if($control instanceof FormControl) {
				$control->set_storage( $storage_name, $store_user );
			}
			$control->class[]= func_get_arg(0);
			$control->container = $this;
			$this->controls[$name]= $control;
		}
		return $control;
	}

	/**
	 * Returns an associative array of the controls' values
	 *
	 * @return array Associative array where key is control's name and value is the control's value
	 */
	public function get_values()
	{
		$values= array();
		foreach ($this->controls as $control) {
			if ($control instanceOf FormContainer) {
				$values= array_merge($values, $control->get_values());
			}
			else {
				$values[$control->name]= $control->value;
			}
		}
		return $values;
	}

	/**
	 * Returns an associative array of controls
	 *
	 * @return array An array of FormControls
	 */
	public function get_controls()
	{
		$controls= array();
		foreach ($this->controls as $control) {
			if ($control instanceOf FormContainer) {
				$controls= array_merge($controls, $control->get_controls());
			}
			else {
				$controls[$control->name]= $control;
			}
		}
		return $controls;
	}

	/**
	 * Moves a control to target's position to which we add $int if specified
	 * That integer is useful to move before or move after the target
	 *
	 * @param FormControl $control FormControl object to move
	 * @param FormControl $target FormControl object acting as destination
	 * @param int $int Integer added to $target's position (index)
	 */
	function move($source, $target, $offset= 0)
	{
		// Remove the source control from its container's list of controls
		$controls = array();
		foreach($source->container->controls as $name => $ctrl) {
			if($ctrl == $source) {
				$source_name = $name;
				continue;
			}
			$controls[$name] = $ctrl;
		}
		$source->container->controls = $controls;

		// Insert the source control into the destination control's container's list of controls in the correct location
		$target_index = array_search($target, array_values($target->container->controls));
		$left_slice= array_slice($target->container->controls, 0, ($target_index + $offset), true);
		$right_slice= array_slice($target->container->controls, ($target_index + $offset), count($target->container->controls), true);

		$target->container->controls = $left_slice + array($source_name => $source) + $right_slice;
	}

	/**
	 * Moves a control before the target control
	 *
	 * @param FormControl $control FormControl object to move
	 * @param FormControl $target FormControl object acting as destination
	 */
	function move_before($control, $target)
	{
		$this->move($control, $target);
	}

	/**
	 * Moves a control after the target control
	 *
	 * @param FormControl $control FormControl object to move
	 * @param FormControl $target FormControl object acting as destination
	 */
	function move_after($control, $target)
	{
		$this->move($control, $target, 1); // Increase left slice's size by one.
	}

	/**
	 * Replaces a target control by the supplied control
	 *
	 * @param FormControl $target FormControl object to replace
	 * @param FormControl $control FormControl object to replace $target with
	 */
	function replace($target, $control)
	{
		$this->move_after($control, $target);
		$this->remove($target);
	}

	/**
	 * Removes a target control from this group (can be the form or a fieldset)
	 *
	 * @param FormControl $target FormControl to remove
	 */
	function remove( $target )
	{
		// Strictness will skip recursiveness, else you get an exception (recursive dependency)
		unset( $this->controls[array_search($target, $this->controls, TRUE)] );
	}

	/**
	 * Retreive the Theme used to display the form component
	 *
	 * @param boolean $forvalidation If true, perform validation on control and add error messages to output
	 * @param FormControl $control The control to output using a template
	 * @return Theme The theme object to display the template for the control
	 */
	function get_theme($forvalidation, $control)
	{
		if(!isset($this->theme_obj)) {
			$theme_dir= Plugins::filter( 'control_theme_dir', Plugins::filter( 'admin_theme_dir', Site::get_dir( 'admin_theme', TRUE ) ) . 'formcontrols/', $control );
			$this->theme_obj= Themes::create( 'admin', 'RawPHPEngine', $theme_dir );
		}
		if($control instanceof FormControl) {
			$this->theme_obj->field= $control->field;
			$this->theme_obj->value= $control->value;
			$this->theme_obj->caption= $control->caption;
			$this->theme_obj->id= (string) $control->id;
			$class= $control->class;
			
			$message= '';
			if($forvalidation) {
				$validate= $control->validate();
				if(count($validate) != 0) {
					$class[]= 'invalid';
					$message= implode('<br>', (array) $validate);
				}
			}
			$this->theme_obj->class= implode( ' ', (array) $class );
			$this->theme_obj->message= $message;
		}
		return $this->theme_obj;
	}

	/**
	 * Returns true if any of the controls this container contains should be stored in userinfo
	 *
	 * @return boolean True if control data should be sotred in userinfo
	 */
	function has_user_options()
	{
		$has_user_options = false;
		foreach($this->controls as $control) {
			$has_user_options |= $control->has_user_options();
		}
		return $has_user_options;
	}


}


/**
 * FormUI Class
 * This will generate the <form> structure and call subsequent controls
 *
 * For a list of options to customize its output or behavior see FormUI::set_option()
 */
class FormUI extends FormContainer
{
	private $success_callback;
	private $success_callback_params = array();
	private static $outpre = false;
	private $options = array(
		'show_form_on_success' => true,
		'save_button' => true,
		'ajax' => false,
		'form_action' => '',
		'on_submit' => '',
	);
	public $class= array( 'formui' );
	public $id= null;

	/**
	 * FormUI's constructor, called on instantiation.
	 *
	 * @param string $name The name of the form, used to differentiate multiple forms.
	 */
	public function __construct( $name )
	{
		$this->name= $name;
	}

	/**
	 * Generate a unique MD5 hash based on the form's name or the control's name.
	 *
	 * @return string Unique string composed of 35 hexadecimal digits representing the victim.
	 */
	public function salted_name()
	{
		return md5(Options::get('secret') . 'added salt, for taste' . $this->name);
	}

	/**
	 * Produce a form with the contained fields.
	 *
	 * @return string HTML form generated from all controls assigned to this form
	 */
	public function get()
	{
		$forvalidation = false;
		$showform = true;
		// Should we be validating?
		if(isset($_POST['FormUI']) && $_POST['FormUI'] == $this->salted_name()) {
			$validate= $this->validate();
			if(count($validate) == 0) {
				$this->success();
				$showform = $this->options['show_form_on_success'];
			}
			else {
				$forvalidation= true;
			}
		}

		$out = '';
		if($showform) {
			$out.= '
				<form method="post" action="'. $this->options['form_action'] .'"'. ( ($this->class) ? ' class="' . implode( " ", (array) $this->class ) . '"' : '' ) . ( ($this->id) ? ' id="' . $this->id . '"' : '' ) .' onsubmit="'. $this->options['on_submit'] .'">
				<input type="hidden" name="FormUI" value="' . $this->salted_name() . '">
			';
			$out.= $this->pre_out_controls();
			$out.= $this->output_controls($forvalidation);

			if($this->options['save_button']) {
				$out.= '<input type="submit" value="save">';
			}

			$out.= '</form>';
		}

		return $out;
	}

	/**
	 * Output a form with the contained fields.
	 * Calls $this->get() and echoes.
	 */
	public function out()
	{
		$args= func_get_args();
		echo call_user_func_array(array($this, 'get'), $args);
	}

	/**
	 * Return the form control HTML.
	 *
	 * @param boolean $forvalidation True if the controls should output additional information based on validation.
	 * @return string The output of controls' HTML.
	 */
	public function output_controls( $forvalidation= false )
	{
		$out= '';
		foreach($this->controls as $control) {
			$out.= $control->out( $forvalidation );
		}
		return $out;
	}

	/**
	 * Return pre-output control configuration scripts for any controls that require them.
	 *
	 * @return string The output of controls' pre-output HTML.
	 */
	public function pre_out_controls( )
	{
		$out= '';
		if(!FormUI::$outpre) {
			FormUI::$outpre = true;
			$out.= '<script type="text/javascript">var controls = Object();</script>';
		}
		foreach($this->controls as $control) {
			$out.= $control->pre_out( );
		}
		return $out;
	}

	/**
	 * Process validation on all controls of this form.
	 *
	 * @return array An array of strings describing validation issues, or an empty array if no issues.
	 */
	public function validate()
	{
		$validate= array();
		foreach($this->controls as $control) {
			$validate= array_merge($validate, $control->validate());
		}
		return $validate;
	}

	/**
	 * Set a function to call on form submission success
	 *
	 * @param mixed $callback A callback function or a plugin filter name.
	 */
	public function on_success( $callback )
	{
		$params = func_get_args();
		$callback = array_shift($params);
		$this->success_callback = $callback;
		$this->success_callback_params = $params;
	}

	/**
	 * Calls the success callback for the form, and optionally saves the form values
	 * to the options table.
	 */
	public function success()
	{
		$result= true;
		if(isset($this->success_callback)) {
			$params = $this->success_callback_params;
			array_unshift($params, $this);
			if(is_callable($this->success_callback)) {
				$result= call_user_func_array($this->success_callback, $params);
			}
			else {
				array_unshift($params, $this->success_callback);
				$result= call_user_func_array(array('Plugins', 'filter'), $params);
			}
		}
		if($result) {
			foreach($this->controls as $control) {
				$control->save();
			}
		}
		if($this->has_user_options()) {
			User::identify()->info->commit();
		}
	}


	/**
	 * Set a form option
	 * Defaults for options are stored in the $this->options array
	 *
	 * @param string $option The name of the option to set
	 * @param mixed $value The value of the option
	 */
	public function set_option( $option, $value )
	{
		$this->options[$option] = $value;
	}

	/**
	 * Configure all the options necessary to make this form work inside a media bar panel
	 * @param string $path Identifies the silo
	 * @param string $panel The panel in the silo to submit to
	 * @param string $callback Javascript function to call on form submission
	 */
	public function media_panel($path, $panel, $callback)
	{
		$this->options['show_form_on_success'] = false;
		//$this->options['save_button'] = false;
		$this->options['ajax'] = true;
		$this->options['form_action'] = URL::get('admin_ajax', array('context' => 'media_panel'));
		$this->options['on_submit'] = "habari.media.submitPanel('$path', '$panel', this, '{$callback}');return false;";
	}

	/**
	 * Magic property getter, returns the value of the specified form control
	 *
	 * @param string $name The name of the control
	 * @return mixed The value of the control
	 */
	public function __get($name)
	{
		$controls = $this->get_controls();
		if(strpos($name, 'user_') === 0) {
			$name = 'user:' . substr($name, 5);
		}
		return $controls[$name];
	}
}

/**
 * FormValidators Class
 *
 * Extend this class to supply your own validators, by default we supply most common
 */
class FormValidators
{

	/**
	 * A validation function that returns an error if the value passed in is not a valid URL.
	 *
	 * @param string $text A string to test if it is a valid URL
	 * @return array An empty array if the string is a valid URL, or an array with strings describing the errors
	 */
	function validate_url( $text )
	{
		if ( !empty( $text ) ) {
			if(!preg_match('/^(?P<protocol>https?):\/\/(?P<domain>[-A-Z0-9.]+)(?P<file>\/[-A-Z0-9+&@#\/%=~_|!:,.;]*)?(?P<parameters>\\?[-A-Z0-9+&@#\/%=~_|!:,.;]*)?/i', $text)) {
				return array(_t('Value must be a valid URL.'));
			}
		}
		return array();
	}
	
	/**
	 * A validation function that returns an error if the value passed in is not a valid Email Address, 
	 * as per RFC2822 and RFC2821.
	 *
	 * @param string $text A string to test if it is a valid Email Address
	 * @return array An empty array if the string is a valid Email Address, or an array with strings describing the errors
	 */
	function validate_email( $text )
	{
		if( !preg_match("@^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*\@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$@i", $text ) ) {
			return array(_t('Value must be a valid Email Address.'));
		}
		return array();
	}

	/**
	 * A validation function that returns an error if the value passed in is not set.
	 *
	 * @param string $text A value to test if it is empty
	 * @return array An empty array if the value exists, or an array with strings describing the errors
	 */
	function validate_required( $value )
	{
		if(empty($value) || $value == '') {
			return array(_t('A value for this field is required.'));
		}
		return array();
	}
	
	/**
	 * A validation function that returns an error if the value passed does not match the regex specified.
	 *
	 * @param string $value A value to test if it is empty
	 * @param FormControl $control The control that defines the value
	 * @param FormContainer $container The container that holds the control
	 * @param string $regex The regular expression to test against
	 * @param string $warning An optional error message	  	  	 	 
	 * @return array An empty array if the value exists, or an array with strings describing the errors
	 */
	function validate_regex( $value, $control, $container, $regex, $warning = NULL )
	{
		if(preg_match($regex, $value)) {
			return array();
		}
		else {
			if ($warning == NULL) {
				$warning= _t('The value does not meet submission requirements');
			}
			else {
				$warning= _t($warning);
			}
			return array($warning);
		}
	}
}

/**
 * A base class from which form controls to be used with FormUI can descend
 */
class FormControl
{
	protected $name;
	protected $caption;
	protected $default;
	protected $validators= array();
	protected $storage;
	protected $store_user = false;
	protected $theme_obj;
	protected $container = null;
	public $id= null;
	public $class= array( 'formcontrol' );

	/**
	 * FormControl constructor - set initial settings of the control
	 *
	 * @param string $name The name of the control
	 * @param string $caption The caption used as the label when displaying a control
	 * @param string $default The default value of the control
	 */
	public function __construct( $name, $caption= null, $default= null )
	{
		$this->name= $name;
		$this->caption= $caption;
		$this->default= $default;
	}


	/**
	 * Set the default value of this control from options or userinfo if the default value isn't explicitly set on creation
	 */
	protected function get_default()
	{
		// Get the default value from Options/UserInfo if it's not set explicitly
		if(empty($this->default)) {
			if($this->store_user) {
				$this->default= User::identify()->info->{$this->storage};
			}
			else {
				$this->default= Options::get( $this->storage );
			}
		}
	}


	/**
	 * Set the Options table key under which this option will be stored
	 *
	 * @param string $key The Options table key to store this option in
	 * @param boolean $store_user True to store the value in userinfo rather than
	 */
	public function set_storage($key, $store_user = false)
	{
		$this->storage= $key;
		$this->store_user = $store_user;
		$this->get_default();
	}

	/**
	 * Store this control's value under the control's specified key.
	 *
	 * @param string $key (optional) The Options table key to store this option in
	 */
	public function save($key= null, $store_user= null)
	{
		if(isset($key)) {
			$this->storage= $key;
		}
		if(isset($store_user)) {
			$this->store_user= $store_user;
		}
		if($this->store_user) {
			User::identify()->info->{$this->storage} = $this->value;
		}
		else {
			Options::set($this->storage, $this->value);
		}
	}

	/**
	 * Return the HTML construction of the control.
	 * Abstract function.
	 *
	 * @param boolean $forvalidation True if the control should output validation information with the control.
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);

		$classname= get_class( $this );
		if(preg_match('%FormControl(.+)%i', $classname, $controltype)) {
			$type.= strtolower($controltype[1]);
		}
		else {
			$type.= strtolower($classname);
		}

		return $theme->fetch( 'formcontrol_' . $type );
	}

	/**
	 * Return the HTML/script required for this type of control.
	 * Abstract function.
	 *
	 */
	public function pre_out()
	{
		return '';
	}

	/**
	 * Runs any attached validation functions to check validation of this control.
	 *
	 * @return array An array of string validation error descriptions or an empty array if no errors were found.
	 */
	public function validate()
	{
		$valid= array();
		foreach($this->validators as $validator) {
			$validator_fn= array_shift($validator);
			if(is_callable($validator_fn)) {
				$params= array_merge(array($this->value, $this, $this->container), $validator);
				$valid= array_merge($valid, call_user_func_array( $validator_fn, $params ) );
			}
			elseif(method_exists('FormValidators', $validator_fn)) {
				$validator_fn= array('FormValidators', $validator_fn);
				$params= array_merge(array($this->value, $this, $this->container), $validator);
				$valid= array_merge($valid, call_user_func_array( $validator_fn, $params ) );
			}
			else {
				$params= array_merge(array($validator_fn, $valid, $this->value, $this, $this->container), $validator);
				$valid= array_merge($valid, call_user_func_array( array('Plugins', 'filter'), $params ) );
			}
		}
		return $valid;
	}

	/**
	 * Magic function __get returns properties for this object.
	 * Potential valid properties:
	 * field: A valid unique name for this control in HTML.
	 * value: The value of the control, whether the default or submitted in the form
	 *
	 * @param string $name The parameter to retrieve
	 * @return mixed The value of the parameter
	 */
	public function __get($name)
	{
		switch($name) {
			case 'field':
				// must be same every time, no spaces
				return sprintf('%x', crc32($this->name));
			case 'value':
				if(isset($_POST[$this->field])) {
					return $_POST[$this->field];
				}
				elseif ($this->store_user && User::identify() && User::identify()->info->{$this->storage} != '') {
					return User::identify()->info->{$this->storage};
				}
				elseif (Options::get($this->storage) != '') {
					return Options::get($this->storage);
				}
				else {
					return $this->default;
				}
		}
		if(isset($this->$name)) {
			return $this->$name;
		}
		return null;
	}

	/**
	 * Returns true if this control should be stored as userinfo
	 *
	 * @return boolean True if this control should be stored as userinfo
	 */
	public function has_user_options()
	{
		return $this->store_user;
	}

	/**
	 * Magic property setter for FormControl and its descendants
	 *
	 * @param string $name The name of the property
	 * @param mixed $value The value to set the property to
	 */
	public function __set($name, $value)
	{
		switch($name) {
			case 'value':
				$this->default = $value;
				break;
			case 'container':
				if($this->container != $value && isset($this->container)) {
					$this->container->remove($this);
				}
				$this->container = $value;
				break;
			case 'id':
				$this->id= (string) $value;
				break;
		}
	}

	/**
	 * Return the theme used to output this control and perform validation if required.
	 *
	 * @param boolean $forvalidation If true, process this control for validation (adds validation failure messages to the theme)
	 * @return Theme The theme that will display this control
	 */
	protected function get_theme($forvalidation)
	{
		return $this->container->get_theme($forvalidation, $this);
	}


	/**
	 * Add a validation function to this control
	 * Multiple parameters are passed as parameters to the validation function
	 * @param mixed $validator A callback function
	 * @param mixed $option... Multiple parameters added to those used to call the validator callback
	 * @return FormControl Returns the control for chained execution	 
	 */
	public function add_validator()
	{
		$args= func_get_args();
		$this->validators[]= $args;
		return $this;
	}

	/**
	 * Move this control before the target
	 * In the end, this will use FormUI::move()
	 *
	 * @param object $target The target control to move this control before
	 */
	function move_before( $target )
	{
		$this->container->move_before( $this, $target );
	}

	/**
	 * Move this control after the target
	 * In the end, this will use FormUI::move()
	 *
	 * @param object $target The target control to move this control after
	 */
	function move_after( $target )
	{
		$this->container->move_after( $this, $target );
	}

}

/**
 * A text control based on FormControl for output via a FormUI.
 */
class FormControlText extends FormControl
{

	/**
	 * Produce HTML output for this text control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);

		return $theme->fetch( 'formcontrol_text' );
	}

}

/**
 * A text control based on FormControl for output via a FormUI.
 */
class FormControlStatic extends FormControl
{

	/**
	 * Produce HTML output for this text control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		return '<div class="static formcontrol">' . $this->caption . '</div>';
	}

	/**
	 * Do not store this static control anywhere
	 *
	 * @param mixed $key Unused
	 * @param mixed $store_user Unused
	 */
	public function save($key= null, $store_user= null)
	{
		// This function should do nothing.
	}

}


/**
 * A password control based on FormControlText for output via a FormUI.
 */
class FormControlPassword extends FormControlText
{

	/**
	 * Produce HTML output for this password control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);
		$theme->outvalue = $this->value == '' ? '' : substr(md5($this->value), 0, 8);

		return $theme->fetch( 'formcontrol_password' );
	}

	/**
	 * Magic function __get returns properties for this object, or passes it on to the parent class
	 * Potential valid properties:
	 * value: The value of the control, whether the default or submitted in the form
	 *
	 * @param string $name The paramter to retrieve
	 * @return mixed The value of the parameter
	 */
	public function __get($name)
	{
		switch($name) {
			case 'value':
				if(isset($_POST[$this->field])) {
					if($_POST[$this->field] == substr(md5($this->default), 0, 8)) {
						return $this->default;
					}
					else {
						return $_POST[$this->field];
					}
				}
				else {
					return $this->default;
				}
			default:
				return parent::__get($name);
		}
	}
}

/**
 * A multiple-slot text control based on FormControl for output via a FormUI.
 * @todo Make DHTML fallback for non-js browsers
 */
class FormControlTextMulti extends FormControl
{
	public static $outpre = false;

	/**
	 * Produce HTML output for this text control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);

		return $theme->fetch( 'formcontrol_textmulti' );
	}

	/**
	 * Return the HTML/script required for this control.  Do it only once.
	 * @return string The HTML/javascript required for this control.
	 */
	public function pre_out()
	{
		$out= '';
		if(!FormControlTextMulti::$outpre) {
			FormControlTextMulti::$outpre = true;
			$out.= '
				<script type="text/javascript">
				controls.textmulti = {
					add: function(e, field){
						$(e).before("<label><input type=\"text\" name=\"" + field + "[]\"> <a href=\"#\" onclick=\"return controls.textmulti.remove(this);\">[' . _t('remove') . ']</a></label>");
						return false;
					},
					remove: function(e){
						if(confirm("' . _t('Remove this item?') . '")) {
							$(e).parents("label").remove();
						}
						return false;
					}
				}
				</script>
			';
		}
		return $out;
	}

}

/**
 * A text control based on FormControl for output via a FormUI.
 */
class FormControlSelect extends FormControl
{
	public $options = array();
	public $multiple = false;
	public $size = 5;

	/**
	 * Override the FormControl constructor to support more parameters
	 * We need to do this because ->value is not a property, we use ->options to store the possible values
	 *
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @param string $selected
	 */
	public function __construct( $name, $caption= null, $options= null, $selected= null )
	{
		$this->name= $name;
		$this->caption= $caption;
		$this->options= $options;
		$this->default= $selected;
	}

	/**
	 * Produce HTML output for this text control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);
		$theme->options = $this->options;
		$theme->multiple = $this->multiple;
		$theme->size = $this->size;

		return $theme->fetch( 'formcontrol_select' );
	}
}

/**
 * A textarea control based on FormControl for output via a FormUI.
 */
class FormControlTextArea extends FormControl
{

	/**
	 * Produce HTML output for this text control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);
		$theme->options = $this->options;

		return $theme->fetch( 'formcontrol_textarea' );
	}
}

/**
 * A checkbox control based on FormControl for output via a FormUI.
 */
class FormControlCheckbox extends FormControl
{

	/**
	 * Produce HTML output for this text control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);

		return $theme->fetch( 'formcontrol_checkbox' );
	}

	/**
	 * Magic __get method for returning property values
	 * Override the handling of the value property to properly return the setting of the checkbox.
	 *
	 * @param string $name The name of the property
	 * @return mixed The value of the requested property
	 */
	public function __get($name)
	{
		switch($name) {
		case 'value':
			if(isset($_POST[$this->field . '_submitted'])) {
				if(isset($_POST[$this->field])) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return $this->default;
			}
		}
		return parent::__get($name);
	}
}

/**
 * A hidden field control based on FormControl for output via a FormUI.
 */
class FormControlHidden extends FormControl
{

	/**
	 * Produce HTML output for this hidden control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	public function out($forvalidation)
	{
		return '<input type="hidden" name="' . $this->field . '" value="' . $this->default . '">';
	}

}

/**
 * A fieldset control based on FormControl for output via a FormUI.
 */
class FormControlFieldset extends FormContainer
{

	public $legend= null;
	public $controls= array();

	/**
	 * Override the FormControl constructor to support more parameters
	 * We want to store the legend, add controls if provided
	 *
	 * @param string $name Name and legend of this fieldset
	 * @param array $controls Array of FormControls to add to this fieldset
	 */
	function __construct( $name, $controls= null )
	{
		$this->name= $name;
		$this->legend= $name;

		if ( is_array($controls) ) {
			$args= $controls;
		}
		elseif ( $controls != '' ) {
			$args= func_get_args();
			if ( count($args) > 1 ) {
				array_shift($args);
			}
			else {
				$args= array();
			}
		}
		else {
			$args= array();
		}
		
		call_user_func_array( array( $this, 'add' ), $args );
	}

	/**
	 * Adds a control or more to this fieldset
	 * You can pass as many parameters as you wish, each will be added
	 */
	function add()
	{
		$controls= func_get_args();
		foreach ( $controls as $control ) {
			$this->controls[]= $control;
			$control->container->remove($control);
			$control->container= $this;
		}
	}

	/**
	 * Removes a target control from this fieldset
	 * You can pass as many parameters as you wish, each will be removed
	 */
	function remove()
	{
		$controls= func_get_args();
		foreach ( $controls as $control ) {
			// Strictness will skip recursiveness, else you get an exception (recursive dependency)
			unset( $this->controls[array_search($control, $this->controls, TRUE)] );
		}
	}

	/**
	 * Move this control before the target
	 * In the end, this will use FormUI::move()
	 *
	 * @param object $target The target control to move this control before
	 */
	function move_before( $target )
	{
		$this->container->move( $this, $target );
	}

	/**
	 * Move this control after the target
	 * In the end, this will use FormUI::move()
	 *
	 * @param object $target The target control to move this control after
	 */
	function move_after( $target )
	{
		$this->move( $this, $target, 1 ); // Increase left slice's size by one.
	}

	/**
	 * Produce HTML output for all this fieldset and all contained controls
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation, $this);
		$contents = '';
		foreach ( $this->controls as $control ) {
			$contents.= $control->out($forvalidation);
		}
		$theme->contents= $contents;
		// Do not move before $contents
		// Else, these variables will contain the last control's values
		$theme->class = $this->class;
		$theme->id = $this->name;
		$theme->legend = $this->legend;

		return $theme->fetch( 'formcontrol_fieldset' );
	}

	/**
	 * Return the HTML/script required for all contained controls.  Do it only once.
	 *
	 * @return string The HTML/javascript required for all contained controls.
	 */
	function pre_out()
	{
		$preout= '';
		foreach ($this->controls as $control) {
			$preout.= $control->pre_out();
		}
		return $preout;
	}

	/**
	 * Runs any attached validation functions to check validation of each control contained in this fieldset.
	 *
	 * @return array An array of string validation error descriptions or an empty array if no errors were found.
	 */
	function validate()
	{
		$results= array();
		foreach($this->controls as $control) {
			if ($result= $control->validate()) {
				$results[]= $result;
			}
		}
		return $results;
	}

	/**
	 * Store each contained control's value under the control's specified key.
	 *
	 * @param string $key (optional) The Options table key to store this option in
	 */
	function save()
	{
		foreach($this->controls as $control) {
			$control->save();
		}
	}

	/**
	 * Returns an associative array of the controls' values
	 * In the end, this will use FormUI::get_values()
	 *
	 * @return array Associative array where key is control's name
	 */
	function get_values()
	{
		return FormUI::get_values();
	}

}

/**
 * A label control based on FormControl for output via a FormUI.
 */
class FormControlLabel extends FormControl
{

	/**
	 * Produce HTML output for this label control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	function out()
	{
		$out= '<div' . (($this->class) ? ' class="' . implode( " ", (array) $this->class ) . '"' : '') . (($this->id) ? ' id="' . $this->id . '"' : '') .'><label for="' . $this->name . '">' . $this->caption . '</label></div>';
		return $out;
	}

}

/**
 * A radio control based on FormControl for output via a FormUI.
 */
class FormControlRadio extends FormControlSelect
{

	/**
	 * Produce HTML output for this radio control.
	 *
	 * @param boolean $forvalidation True if this control should render error information based on validation.
	 * @return string HTML that will render this control in the form
	 */
	function out($forvalidation)
	{
		$theme= $this->get_theme($forvalidation);
		$theme->options = $this->options;

		return $theme->fetch( 'formcontrol_radio' );
	}

}

?>
