<?php
/**
 * Class to build & manage a contact form
 *
 * @package AlienContact
 * @todo Add documentation
 * @todo Add interface to database
 */

class AlienContact extends Plugin {
	private $theme= null;
	
	function info() {
		return array(
			'name' => 'AlienContact',
			'version' => '1.0',
			'url' => 'http://myfla.ws/projects/contact/',
			'author' => 'Arthus Erea',
			'authorurl' => 'http://myfla.ws',
			'license' => 'Creative Commons Attribution-Share Alike 3.0',
			'description' => 'AlienContact generates a form for visitors to contact you',
		);
	}
	function elements() {
		return array(
			'name' => array(
				'id' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'regex' => '/^.{2,}$/',
				'required' => TRUE
			),
			'email' => array(
				'id' => 'email',
				'type' => 'text',
				'label' => 'Email',
				'regex' => "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",
				'required' => TRUE
			),
			'url' => array(
				'id' => 'url',
				'type' => 'text',
				'label' => 'Website',
				'regex' => '/(http|https):\/\/(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',
				'required' => FALSE
			),
			'message' => array(
				'id' => 'message',
				'type' => 'textarea',
				'label' => 'Message',
				'regex' => '/^[\s\S]{2,}$/',
				'required' => TRUE
			),
			'submit' => array(
				'id' => 'submit',
				'type' => 'submit',
				'label' => 'Send',
				'regex' => '',
				'required' => FALSE
			)
		);
	}
	public function filter_rewrite_rules( $rules ) {
		$rules[] = new RewriteRule(array(
			'name' => 'aliencontact',
			'parse_regex' => '%scripts/aliencontact.js$%i',
			'build_str' =>  'scripts/aliencontact.js',
			'handler' => 'UserThemeHandler',
			'action' => 'display_js',
			'priority' => 6,
			'is_active' => 1,
		));
		
		return $rules;
	}
	public function filter_post_content ($content) {
		$url = Site::get_url('host') . Controller::get_full_url();
		$input = 'post';
		
		$form = self::get_form($url, $input);
		
		$content = str_replace('<!--contactForm-->', $form, $content);
		
		return $content;
	}
	public function action_handler_display_js($handler_vars) {
		
		
		$url = URL::get('ajax', array('context'=>'submit_form'));
		
		include('aliencontact.js.php');
		
		exit;
	}
	public function action_ajax_submit_form( $handler ) {
		
		echo self::get_form('', $_POST);
		
	}
	public function filter_plugin_config( $actions, $plugin_id ) {
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}
	public function action_plugin_ui( $plugin_id, $action ) {
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$email= $ui->add( 'text', 'email', _t('Email:') );
					$subject= $ui->add( 'text', 'subject', _t('Email Subject:') );
					$database= $ui->add( 'checkbox', 'database', _t('Use Database') );
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
				break;
			}
		}
	}
	public function updated_config ( $ui ) {
		return TRUE;
	}
	public function action_init_theme() {
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/aliencontact.js', 'aliencontact' );
		Stack::add( 'template_stylesheet', array( URL::get_from_filesystem(__FILE__) . '/aliencontact.css', 'screen' ), 'aliencontactcss' );
	}
	/**
	 * Prints the form
	 *
	 */
	function form() {
		$url = Site::get_url('host') . Controller::get_full_url();
		$input = 'post';
		
		echo self::get_form($url, $input);
	}
	/**
	 * Fetch the form
	 *
 	 * @param string $action where the form should be sent to (usually current URL)
	 * @param string $input where input is coming from (only 'post' works now)
	 * @return string the form
	 */
	public function get_form($action = '', $input = 'post') {
		
		$elements = self::elements();
		
		$output = '';
		$values = array();
		$errors = array();
		
		if($input == 'post') {
			foreach($elements as $key => $field) {
				if(isset($_POST['contactForm_' . $key])) {
					$value = $_POST['contactForm_' . $key];
					if(AlienContact::check_input($value, $key) != '') {
						$errors[$key] = AlienContact::check_input($value, $key);
						$values[$key] = $value;
					} else {
						$values[$key] = $value;
					}
				}
			}
		} else {
			foreach($elements as $key => $field) {
				if(isset($input[$key])) {
					$value = $input[$key];
					if(AlienContact::check_input($value, $key) != '') {
						$errors[$key] = AlienContact::check_input($value, $key);
						$values[$key] = $value;
					} else {
						$values[$key] = $value;
					}
				}
			}
		}
		
		if(count($errors) != 0) {
			$output .= '<div class="errors">';
			$output .= '<h3>Errors</h3>';
			$output .= '<ol>';
			foreach($errors as $id => $error) {
				$output .= '<li id="contactForm_'.$id.'_error"><a href="#contactForm_'.$id.'_div" title="Fix the error">'.$error.'</a>.</li>';
			}
			$output .= '</ol>';
			$output .= '</div>';
			$output .= self::make_form($action, $values, $errors);
		} elseif(count($values) > 0) {
			if(self::submit($values)) {
				$output .= '<div class="success">';
				$output .= '<h3>Success</h3>';
				$output .= '<p>Your message has successfully been delivered.</p>';
				$output .= '</div>';
			} else {
				$output .= '<div class="errors">';
				$output .= '<h3>Error</h3>';
				$output .= '<p>Uh-oh... looks like I made a mistake. What can you expect? I am only a web server after all. Sorry... 1,000 times sorry. Please, give me another chance! I&apos;m begging you! Will you ever talk to me again?';
				$output .= '</div>';
				$output .= self::make_form($action, $values, $errors);
			}
		} else {
			$output .= self::make_form($action, $values, $errors);
		}
		
		return $output;
	}
	public function check_input($value, $key) {
		$elements = self::elements();
		
		$value = trim($value);
		
		if( ($elements[$key]['required'] and preg_match($elements[$key]['regex'], $value)) or (($elements[$key]['required'] == FALSE) && (strlen($value) != 0) && ($elements[$key]['regex'] != '') && (preg_match($elements[$key]['regex'], $value))) or (($elements[$key]['required'] == FALSE) && ($elements[$key]['regex'] == '')) ) {
			$error = '';
		} elseif(($elements[$key]['required'] == FALSE) and (strlen($value) == 0)) {
			$error = '';
		} elseif(($elements[$key]['required'] == TRUE) and (strlen($value) == 0)) {
			$error = 'You must enter a ' .  $elements[$key]['id'];
		} else {
			$error = 'Your ' . $elements[$key]['id'] . ' failed to validate.';
		}
		
		return $error;
	}
	function install() {
		if(Options::get('aliencontact:database')) {
			DB::register_table('aliencontact');

			$sql = "CREATE TABLE " . DB::table('aliencontact') . " (
				id int(9) NOT NULL AUTO_INCREMENT,
				time int(12) NOT NULL,
				updated int(12) NOT NULL,
				ip varchar(20) NOT NULL,
				val blob  NOT NULL,
				UNIQUE KEY id (id)
			);";

			$sql = DB::dbdelta($sql);
			
			return TRUE;
		} else {
			return FALSE;
		}

	}
	function insert($input) {
		
		$insert = array();
		$insert['time'] = time();
		$insert['updated'] = time();
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		$insert['val'] = serialize($input);
		
		return DB::insert(DB::table('aliencontact'), $insert);
		
	}
	function submit($input) {
		
		$elements = self::elements();
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		$message = 'You have recieved a submission through your online form. The message follows.<br />';
		
		foreach($input as $field => $value) {
			$message .= $elements[$field]['label'] . ': ';
			$message .= $value;
			$message .= '<br />';
		}
		
		if(mail(Options::get('aliencontact:email'), Options::get('aliencontact:subject'), $message) == TRUE) {
			if(self::install()) {
				if(self::insert($input)) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
	function make_form($action = '', $input = NULL, $errors = NULL) {
		
		$elements = self::elements();
		
		if($errors == NULL) {
			unset($errors);
		}
		
		$output = '<form id="contactForm" name="contactForm" action="' . $action . '" method="post" class=""';
		$output .= '>';
		
		foreach($elements as $key => $element) {
				$output .= '<div id="contactForm_'.$key.'_div" class="container field '.$element['type'] . ' ' . $key;
				$output .= ($element['required']) ? ' required' : '';
				if(isset($errors) && isset($errors[$key]) && $errors[$key] != '') {
					$output .= ' bad';
				} elseif(isset($errors)) {
					$output .= ' good';
				}
				$output .= '">';
				
				switch($element['type']) {
					case 'text':
						$output .= '<label id="contactForm_'.$key.'_label" for="contactForm_'.$key.'" class="contactForm label">'.$element['label'].'</label>';
						$output .= '<input id="contactForm_'.$key.'" name="contactForm_'.$key.'" type="text" class="contactForm '.$element['type'].' '.$element['id'].'" value="';
						if(isset($input[$key])) {
							$output .= $input[$key];
						}
						$output .= '" />';
						if(isset($element['info'])) {
							$output .= '<p id="contactForm_'.$key.'_info" class="contactForm_info">' . $element['info'] . '</p>'; 
						}
					break;
					case 'textarea':
						$output .= '<label id="contactForm_'.$key.'_label" for="contactForm_'.$key.'" class="contactForm label">'.$element['label'].'</label>';
						$output .= '<textarea id="contactForm_'.$key.'" name="contactForm_'.$key.'" class="contactForm '.$element['type'].' '.$element['id'].'" rows="3" cols="30">';
						if(isset($input[$key])) {
							$output .= $input[$key];
						}
						$output .= '</textarea>';
						if(isset($element['info'])) {
							$output .= '<p id="contactForm_'.$key.'_info" class="contactForm_info">' . $element['info'] . '</p>'; 
						}
					break;
					case 'submit':
						$output .= '<input type="submit" name="contactForm_'.$key.'" id="contactForm_'.$key.'" value="'.$element['label'].'" />';
					break;
				}
				
				if(isset($errors[$key]) && $errors[$key] != '') {
					$output .= '<a href="#contactForm_'.$key.'_error" title="' . $errors[$key] . '" class="error" class="error">Error</a>';
				}
				
				$output .= "</div>\n";
		}
		
		$output .= "</form>";
		
		return $output;
	}
}
?>