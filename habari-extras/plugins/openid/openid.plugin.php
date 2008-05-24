<?php
class OpenID extends Plugin
{
	public function info() {
		return array(
			'name' => 'OpenID',
			'version' => '1.1.1',
			'url' => 'http://phpquebec.org/',
			'author' =>	'PHP Quebec Community',
			'authorurl' => 'http://phpquebec.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Adds OpenID 2.0 authentification support.',
			'copyright' => '2007'
			);
	}
	
	public function filter_rewrite_rules( $db_rules ) {
		$db_rules[]= new RewriteRule( array(
			'name' => 'openid',
			'parse_regex' => '%^openid/?(?P<user>[^/]*)/?$%i', // For Server, if previous matched, don't look.
			'build_str' => 'openid/({$user})',
			'handler' => 'OpenID',
			'action' => 'dispatch',
			'priority' => 1,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'OpenID Authentification'
			) );
		
		return $db_rules;
	}

	public function act( $action ) {
		if ( isset( $_GET['openid_mode'] ) ) {
			switch ( $_GET['openid_mode'] ) {
				case 'id_res':
					self::openid_end();
					break;
				case 'cancel':
					throw new Exception( 'Authorization failed: user cancelled the authorization.' );
					break;
			}
		}
		else if ( isset( $_POST['openid_url'] ) ) {
			self::openid_start();
		}
		else {
			throw new Exception( 'Authorization failed: unknown error.' );
		}
	}

	public function action_plugin_activation( $file ) {		
		if ( realpath( $file ) == __FILE__ ) {
			if ( !extension_loaded('curl') && !@dl('curl') ) {
				throw new Exception( 'Could not load CURL, you need CURL for OpenID to work.' );
			}
			EventLog::register_type( 'authentification', 'OpenID' );
		}
	}

	public function action_plugin_deactivation( $file ) {
		if ( realpath( $file ) == __FILE__ ) {
			EventLog::unregister_type( 'OpenID' );
		}
	}

	public function action_init() {
		if ( session_id() == '' ) {
			session_start();
		}
		ini_set( 'include_path', Site::get_dir( 'user' ).'/plugins/openid/' );
		Stack::add( 'template_stylesheet', array( Site::get_url('user').'/plugins/openid/openid.css', 'screen' ), 'openid_style' );
	}

	public function action_theme_loginform_before() {
		if ( isset( $_GET['openid_url'] ) ) {
			echo '<hr><div class="alert"><strong>If you have an existing account</strong>, sign in so we can assign your OpenID identifer to it.</div>';
		}
	}
	
	public function action_theme_loginform_after() {
	 // @todo Remove the !isset( $_GET['openid_url'] ) once registration works in Habari.
		if ( ( Controller::get_action() != 'register' ) && !isset( $_GET['openid_url'] ) ) {
			echo '
				<form method="post" action="'. URL::get( 'openid' ) .'" id="openidform">
				<p>
				<label for="openid_url">OpenID Identifier:</label>
				<input type="text" size="25" name="openid_url" id="openid_url">
				</p>
				<p>
				<input type="submit" value="Sign in using OpenID">
				</p>
				</form>
				';
		}
	}

	public function action_theme_loginform_controls() {
		if ( isset( $_GET['openid_url'] ) ) {
			echo '<input type="hidden" value="'.$_GET['openid_url'].'" name="habari_openid_url">';
		}
	}
	
	/* Uncomment once registration is supported by Habari.
	public function action_theme_registerform_controls() {
		if ( isset( $_GET['openid_url'] ) ) {
			echo '<input type="hidden" value="'.$_GET['openid_url'].'" name="habari_openid_url">';
		}
	} */

	public function action_theme_admin_user( $user ) {
		$openid_url= isset( $user->info->openid_url ) ? $user->info->openid_url : '';
		echo '<p><label for="openid_url">OpenID Identifier:</label></p>';
		echo '<p><input type="text" value="'.$openid_url.'" name="habari_openid_url" disabled></p>';
	}
	
	public function action_user_identify() {
		if ( ( Controller::get_action() == 'login' ) && !empty( $_POST['openid_url'] ) ) {
			self::openid_start();
		}
	}

	// TODO: Add more security against form hijacking (for instance, check against server sent data)
	public function action_user_authenticate_successful( $user ) {
		if ( !empty( $_POST['habari_openid_url'] ) ) {
			$user->info->openid_url= $_POST['habari_openid_url'];
		}
	}

	function getOpenIDURL() {
		if ( empty( $_POST['openid_url'] ) ) {
			throw new Exception( 'Expected an OpenID URL.' );
		}
		
		return $_POST['openid_url'];
	}
	
	function getReturnTo() {
		return URL::get('openid');
	}
	
	function getTrustRoot() {
		return Site::get_url('habari');
	}
	
	function getStore() {
		$store_path= "/tmp/_php_consumer_test";
		
		if ( !file_exists( $store_path ) && !mkdir( $store_path ) ) {
			throw new Exception( 'Could not create the FileStore directory: ' . $store_path . '. Please check the effective permissions.' );
		}
		
		return new Auth_OpenID_FileStore( $store_path );
	}
	
	function getConsumer() {
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		require_once "Auth/OpenID/SReg.php";
		$store= self::getStore();
		return new Auth_OpenID_Consumer( $store );
	}
	
	function openid_start() {
		$openid= self::getOpenIDURL();
		$consumer= self::getConsumer();
		
		$auth_request= $consumer->begin( $openid );
		
		if ( !$auth_request ) {
			throw new Exception( 'Authentication error; not a valid OpenID.' );
		}
		
		$sreg_request= Auth_OpenID_SRegRequest::build( array( 'nickname' ), array( 'fullname', 'email' ) );
		
		if ( $sreg_request ) {
			$auth_request->addExtension( $sreg_request );
		}
		
		if ( $auth_request->shouldSendRedirect() ) {
			$redirect_url= $auth_request->redirectURL( self::getTrustRoot(), self::getReturnTo() );
			
			if ( Auth_OpenID::isFailure( $redirect_url ) ) {
				throw new Exception( 'Could not redirect to server: ' . $redirect_url->message );
			}
			else {
				header( "Location: ".$redirect_url );
			}
		}
		else {
			$form_id= 'openid_message';
			$form_html= $auth_request->formMarkup( self::getTrustRoot(), self::getReturnTo(), false, array( 'id' => $form_id ) );
			
			if ( Auth_OpenID::isFailure( $form_html ) ) {
				throw new Exception( 'Could not prepare redirection form: ' . $form_html->message );
			}
			else {
				echo '
					<html>
					<head>
					<title>OpenID transaction in progress</title>
					</head>
					<body onload="document.getElementById(\''.$form_id.'\').submit()">
					'.$form_html.'
					</body>
					</html>
					';
			}
		}
	}
	
	function openid_end() {
		$consumer= self::getConsumer();
		$return_to= self::getReturnTo();
		$response= $consumer->complete( $return_to );
		
		switch( $response->status ) {
			case Auth_OpenID_CANCEL:
				throw new Exception( 'Verification cancelled.' );
				break;
			case Auth_OpenID_FAILURE:
				throw new Exception( 'OpenID authentication failed: ' . $response->message );
				break;
			case Auth_OpenID_SUCCESS:
				$openid= $response->getDisplayIdentifier();
				$esc_identity= htmlspecialchars( $openid, ENT_QUOTES );
				
				$user= Users::get_by_info( 'openid_url', $openid );
				if ( count( $user ) != 0 ) {
					if ( count( $user ) > 1 ) {
						throw new Exception( 'Authentication error: more than one user have this OpenID.' );
					}
					$user[0]->remember();
					EventLog::log( 'Successful login for ' . $user[0]->username, 'info', 'authentication', 'OpenID' );
					
					header( "HTTP/1.1 100 Continue" );
					header( "Location: " . Site::get_url( 'admin' ) );
					header( "Connection: close" );
				}
				else {
					Utils::redirect( URL::get( 'user', array( 'page'=>'login', 'openid_url' => $openid ), true ) );
				}
			}
		}

}
?>
