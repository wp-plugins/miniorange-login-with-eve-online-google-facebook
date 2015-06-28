<?php
/**
* Plugin Name: miniOrange OAuth Login
* Plugin URI: http://miniorange.com
* Description: This plugin enables login to your Wordpress site using apps like EVE Online, Google, Facebook.
* Version: 1.0.0
* Author: miniOrange
* Author URI: http://miniorange.com
* License: GPL2
*/
include_once dirname( __FILE__ ) . '/class-mo-oauth-widget.php';
require('class-customer.php');
require('mo_oauth_settings_page.php');

class mo_oauth {
	
	function __construct() {
		add_action( 'admin_menu', array( $this, 'miniorange_menu' ) );
		add_action( 'admin_init',  array( $this, 'miniorange_oauth_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'mo_login_widget_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_oauth_deactivate'));
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
	}
 	
	function mo_oauth_success_message() {
		$class = "error";
		$message = get_option('message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>"; 
	}
	
	function mo_oauth_error_message() {
		$class = "updated";
		$message = get_option('message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>"; 
	}
	
	public function mo_oauth_deactivate() {
		//delete all stored key-value pairs
		delete_option('host_name');
		delete_option('new_registration');
		delete_option('mo_oauth_admin_phone');
		delete_option('verify_customer');
		delete_option('mo_oauth_admin_customer_key');
		delete_option('mo_oauth_admin_api_key');
		delete_option('customer_token');
		delete_option('mo_oauth_google_enable');
		delete_option('mo_oauth_google_scope');
		delete_option('mo_oauth_google_client_id');
		delete_option('mo_oauth_google_client_secret');
		delete_option('mo_oauth_google_redirect_url');
		delete_option('mo_oauth_google_message');
		delete_option('mo_oauth_eveonline_enable');
		delete_option('mo_oauth_eveonline_scope');
		delete_option('mo_oauth_eveonline_client_id');
		delete_option('mo_oauth_eveonline_client_secret');
		delete_option('mo_oauth_eveonline_redirect_url');
		delete_option('mo_oauth_eveonline_message');
		delete_option('message');
		delete_option('mo_eve_api_key');
		delete_option('mo_eve_verification_code');
		delete_option('mo_eve_allowed_corps');
		delete_option('mo_eve_allowed_alliances');
		delete_option('mo_eve_allowed_char_name');
	}
	
	private $settings = array(
		'mo_oauth_facebook_client_secret'	=> '',
		'mo_oauth_facebook_client_id' 		=> '',
		'mo_oauth_facebook_enabled' 		=> 0
	);
	
	function miniorange_menu() {
		
		//Add miniOrange plugin to the menu
		$page = add_menu_page( 'MO OAuth Settings ' . __( 'Configure OAuth', 'mo_oauth_settings' ), 'miniOrange OAuth', 'administrator', 'mo_oauth_settings', array( $this, 'mo_oauth_login_options' ) );

		//Eve Online Setup
		$page = add_submenu_page( 'mo_oauth_settings', 'MO Login ' . __('Advanced EVE Online Settings'), __('Advanced EVE Online Settings'), 'administrator', 'mo_oauth_eve_online_setup', 'mo_eve_online_config' );
		
		global $submenu;
		if ( is_array( $submenu ) AND isset( $submenu['mo_oauth_settings'] ) )
		{
			$submenu['mo_oauth_settings'][0][0] = __( 'Configure OAuth', 'mo_oauth_login' );
		}
	}
	
	function  mo_oauth_login_options () {
		global $wpdb;
		update_option( 'host_name', 'https://auth.miniorange.com' );
		$customerRegistered = mo_oauth_is_customer_registered();
		if( $customerRegistered ) {
			mo_register();
		} else {
			mo_register();
		}	
	}
	
	function plugin_settings_style() {
		wp_enqueue_style( 'mo_oauth_admin_settings_style', plugins_url( 'miniorange-oauth-login/style_settings.css' ) );
	}
	
	function plugin_settings_script() {
		wp_enqueue_script( 'mo_oauth_admin_settings_script', plugins_url( 'miniorange-oauth-login/settings.js' ), array( 'jquery-form','jquery-effects-blind' ) );
	}
	
	function mo_login_widget_text_domain(){
		load_plugin_textdomain( 'flw', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	private function mo_oauth_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
	}
	
	private function mo_oauth_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
	}
	
	public function mo_oauth_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	function miniorange_oauth_save_settings(){
		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_register_customer" ) {	//register the admin to miniOrange
			
			//validation and sanitization
			$email = '';
			$phone = '';
			$password = '';
			$confirmPassword = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['email'] ) || $this->mo_oauth_check_empty_or_null( $_POST['phone'] ) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) || $this->mo_oauth_check_empty_or_null( $_POST['confirmPassword'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter valid entries.');
				$this->mo_oauth_show_error_message();
				return;
			} else if( strlen( $_POST['password'] ) < 8 || strlen( $_POST['confirmPassword'] ) < 8){
				update_option( 'message', 'Choose a password with minimum length 8.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$phone = sanitize_text_field( $_POST['phone'] );
				$password = sanitize_text_field( $_POST['password'] );
				$confirmPassword = sanitize_text_field( $_POST['confirmPassword'] );
			}
			
			update_option( 'mo_oauth_admin_email', $email );
			update_option( 'mo_oauth_admin_phone', $phone );
			if( strcmp( $password, $confirmPassword) == 0 ) {
				update_option( 'password', $password );
				$customer = new Customer();
				$customerKey = json_decode( $customer->create_customer(), true );
				if( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
					$content = $customer->get_customer_key();
					$customerKey = json_decode( $content, true );
					if( json_last_error() == JSON_ERROR_NONE ) {
						update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
						update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
						update_option( 'customer_token', $customerKey['token'] );
						update_option('password', '' );
						update_option( 'message', 'Customer retrieved successfully' );
						delete_option('verify_customer');
						delete_option('new_registration');
						$this->mo_oauth_show_success_message();
						//mo_register();
					} else {
						update_option( 'message', 'You already have an account with miniOrange. Please enter a valid password.');
						update_option('verify_customer', 'true');
						delete_option('new_registration');
						//mo_register();
						$this->mo_oauth_show_error_message();
						
					}
				} else if( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
					update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
					update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
					update_option( 'customer_token', $customerKey['token'] );
					update_option('password', '');
					update_option( 'message', 'Registered successfully.');
					delete_option('verify_customer');
					delete_option('new_registration');
					$this->mo_oauth_show_success_message();
				}
			} else {
				update_option( 'message', 'Passwords do not match.');
				delete_option('verify_customer');
				$this->mo_oauth_show_error_message();
			}
			update_option('password', '');
		}
		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_verify_customer" ) {	//register the admin to miniOrange
			
			//validation and sanitization
			$email = '';
			$password = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['email'] ) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter valid entries.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$password = sanitize_text_field( $_POST['password'] );
			}
		
			update_option( 'mo_oauth_admin_email', $email );
			update_option( 'password', $password );
			$customer = new Customer();
			$content = $customer->get_customer_key();
			$customerKey = json_decode( $content, true );
			if( json_last_error() == JSON_ERROR_NONE ) {
				update_option( 'mo_oauth_admin_customer_key', $customerKey['id'] );
				update_option( 'mo_oauth_admin_api_key', $customerKey['apiKey'] );
				update_option( 'customer_token', $customerKey['token'] );
				update_option( 'mo_oauth_admin_phone', $customerKey['phone'] );
				update_option('password', '');
				update_option( 'message', 'Customer retrieved successfully');
				delete_option('verify_customer');
				$this->mo_oauth_show_success_message(); 
			} else {
				update_option( 'message', 'Invalid username or password. Please try again.');
				$this->mo_oauth_show_error_message();		
			}
			update_option('password', '');
		}
		//save API KEY for eveonline from eveonline setup
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_eve_save_api_key" ){
			
			//validation and sanitization
			$apiKey = '';
			$verificationCode = '';
			if( $this->mo_oauth_check_empty_or_null( $_POST['mo_eve_api_key'] ) || $this->mo_oauth_check_empty_or_null( $_POST['mo_eve_verification_code'] ) ) {
				update_option( 'message', 'All the fields are required. Please enter Key ID and Verfication code to save API Key details.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$apiKey = sanitize_text_field( $_POST['mo_eve_api_key'] );
				$verificationCode = sanitize_text_field( $_POST['mo_eve_verification_code'] );
			}
			
			update_option( 'mo_eve_api_key' ,$apiKey);
			update_option('mo_eve_verification_code', $verificationCode);
			if( get_option('mo_eve_api_key') && get_option('mo_eve_verification_code') ) {
				update_option( 'message', 'Your API Key details have been saved');
				$this->mo_oauth_show_success_message();
			} else {
				update_option( 'message', 'Please enter Key ID and Verfication code to save API Key details');
				$this->mo_oauth_show_error_message();
			}
		}
		//save allowed corporations, alliances and character names
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_eve_save_allowed" ){
			
			//sanitization of corporations and alliance fields
			$corps = sanitize_text_field( $_POST['mo_eve_allowed_corps'] );
			$alliances = sanitize_text_field( $_POST['mo_eve_allowed_alliances'] );
			$charName = sanitize_text_field( $_POST['mo_eve_allowed_char_name'] );
			
			update_option( 'mo_eve_allowed_corps' ,$corps );
			update_option( 'mo_eve_allowed_alliances', $alliances );
			update_option( 'mo_eve_allowed_char_name', $charName );
			if( get_option('mo_eve_allowed_corps') || get_option('mo_eve_allowed_alliances') || get_option('mo_eve_allowed_char_name') ) {
				if( get_option('mo_eve_api_key') && get_option('mo_eve_verification_code') ) {
					update_option( 'message', 'Your allowed Corporations, Alliances and Characters have been saved');
					$this->mo_oauth_show_success_message();
				} else {
					update_option( 'message', 'Please enter Key ID and Verification code to filter Characters. Your allowed Corporations, Alliances and Characters have been saved.');
					$this->mo_oauth_show_error_message();
				}
			} else {
				if( get_option('mo_eve_api_key') && get_option('mo_eve_verification_code') ) {
					update_option( 'message', 'Characters of all Corporations and Alliances will be allowed.');
					$this->mo_oauth_show_success_message();
				} else {
					update_option( 'message', 'Please enter Key ID and Verification code to filter Characters. Characters of all Corporations and Alliances will be allowed.');
					$this->mo_oauth_show_error_message();
				}
			}
		}
		//submit google form
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_oauth_google" ) {
			
			//validation and sanitization
			$scope = '';
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_google_scope']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_google_client_id']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_google_client_secret'])) {
				update_option( 'message', 'Please enter Client ID and Client Secret to save settings.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$scope = sanitize_text_field( $_POST['mo_oauth_google_scope'] );
				$clientid = sanitize_text_field( $_POST['mo_oauth_google_client_id'] );
				$clientsecret = sanitize_text_field( $_POST['mo_oauth_google_client_secret'] );
			}
			
			if(mo_oauth_is_customer_registered()) {
				update_option( 'mo_oauth_google_enable', isset( $_POST['mo_oauth_google_enable']) ? $_POST['mo_oauth_google_enable'] : 0);
				update_option( 'mo_oauth_google_scope', $scope);
				update_option( 'mo_oauth_google_client_id', $clientid);
				update_option( 'mo_oauth_google_client_secret', $clientsecret);
				update_option( 'mo_oauth_google_redirect_url', sanitize_text_field($_POST['mo_oauth_google_redirect_url']));
				if(get_option('mo_oauth_google_client_id') && get_option('mo_oauth_google_client_secret')) {
					$customer = new Customer();
					$message = $customer->add_oauth_application( 'google', 'Google OAuth' );
					if($message == 'Application Created') {
						update_option( 'message', 'Your settings were saved' );
						$this->mo_oauth_show_success_message();
					} else {
						update_option( 'message', $message );
						$this->mo_oauth_show_error_message();
					}
				} else {
					update_option( 'message', 'Please enter Client ID and Client Secret to save settings');
					update_option( 'mo_oauth_google_enable', false);
					$this->mo_oauth_show_error_message();
				}
			} else {
				update_option('message', 'Please register customer before trying to save other configurations');
				$this->mo_oauth_show_error_message();
			}
		} 
		//submit eveonline form
		else if(isset($_POST['option']) and $_POST['option'] == "mo_oauth_eveonline"){
			
			//validation and sanitization
			$clientid = '';
			$clientsecret = '';
			if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_eveonline_client_secret']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_eveonline_client_secret'])) {
				update_option( 'message', 'Please enter Client ID and Client Secret to save settings.');
				$this->mo_oauth_show_error_message();
				return;
			} else{
				$clientid = sanitize_text_field($_POST['mo_oauth_eveonline_client_secret']);
				$clientsecret = sanitize_text_field($_POST['mo_oauth_eveonline_client_secret']);
			}
			
			if(mo_oauth_is_customer_registered()) {
				update_option( 'mo_oauth_eveonline_enable', isset($_POST['mo_oauth_eveonline_enable']) ? $_POST['mo_oauth_eveonline_enable'] : 0);
				update_option( 'mo_oauth_eveonline_client_id', $clientid);
				update_option( 'mo_oauth_eveonline_client_secret', $clientsecret);
				update_option( 'mo_oauth_eveonline_redirect_url', sanitize_text_field($_POST['mo_oauth_eveonline_redirect_url']));
				if(get_option('mo_oauth_eveonline_client_id') && get_option('mo_oauth_eveonline_client_secret')) {
					$customer = new Customer();
					$message = $customer->add_oauth_application('eveonline', 'EVE Online OAuth');
					if($message == 'Application Created') {
						update_option('message', 'Your settings were saved. Go to Advanced EVE Online Settings for configuring restrictions on user sign in.');
						$this->mo_oauth_show_success_message();
					} else {
						update_option('message', $message);
						$this->mo_oauth_show_error_message();
					}
				} else {
					update_option( 'message', 'Please enter Client ID and Client Secret to save settings');
					update_option( 'mo_oauth_eveonline_enable', false);
					$this->mo_oauth_show_error_message();
				}
			} else {
				update_option('message', 'Please register customer before trying to save other configurations');
				$this->mo_oauth_show_error_message();
			}
		}
		
	}
}

	function mo_oauth_my_show_extra_profile_fields($user) {
		?>
		<h3>Extra profile information</h3>
		<table class="form-table">
			<tr>
				<th><label for="corporation">Corporation Name</label></th>
				<td>
					<input type="text" id="corporation" disabled="true" value="<?php echo get_user_meta( $user->ID, 'user_eveonline_corporation_name', true ); ?>" class="regular-text" /><br />
				</td>
				<td rowspan="3"><img src="http://image.eveonline.com/Character/<?php echo get_user_meta($user->ID, 'user_eveonline_character_id', true);?>_256.jpg"/></td>
			</tr>
			<tr>
				<th><label for="alliance">Alliance Name</label></th>
				<td>
					<input type="text" id="alliance" disabled="true" value="<?php echo get_user_meta( $user->ID, 'user_eveonline_alliance_name', true ); ?>" class="regular-text" /><br />
				</td>
			</tr>
			<tr>
				<th><label for="characterName">Character Name</label></th>
				<td>
					<input type="text" id="characterName" disabled="true" value="<?php echo get_user_meta( $user->ID, 'user_eveonline_character_name', true ); ?>" class="regular-text" /><br />
				</td>
			</tr>
		</table>
	<?php
	}

	function mo_oauth_is_customer_registered() {
		$email 			= get_option('mo_oauth_admin_email');
		$phone 			= get_option('mo_oauth_admin_phone');
		$customerKey 	= get_option('mo_oauth_admin_customer_key');
		if( ! $email || ! $phone || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}

new mo_oauth;