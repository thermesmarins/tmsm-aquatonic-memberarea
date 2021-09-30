<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/public
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Memberarea_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Get locale
	 */
	private function get_locale() {
		return (function_exists('pll_current_language') ? pll_current_language() : substr(get_locale(),0, 2));
	}


	/**
	 * Get option
	 * @param string $option_name
	 *
	 * @return null
	 */
	private function get_option($option_name = null){

		$options = get_option($this->plugin_name . '-options');

		if(!empty($option_name)){
			return $options[$option_name] ?? null;
		}
		else{
			return $options;
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-memberarea-public.css', array('theme'), $this->version, 'all' );

		// Define inline css
		$css 			= '';

		// Return CSS
		if ( ! empty( $css ) ) {
			$css = '/* Aquatonic Memberarea CSS */'. $css;
			wp_add_inline_style( $this->plugin_name, $css );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-memberarea-public.js', array( 'jquery', 'backbone', 'wp-util' ), $this->version, true );



		// Params
		$params = [
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'tmsm-aquatonic-memberarea-nonce-action' ),
			'locale'   => $this->get_locale(),
			'page' => get_permalink($this->get_option('pageid')),
			'i18n'     => [
				'memberarea'          => __( 'Live Memberarea', 'tmsm-aquatonic-memberarea' ),
				'moreinfo'          => __( 'More Info About Memberarea', 'tmsm-aquatonic-memberarea' ),
			],
			'data'     => [
				'realtime' => [],
			],
		];

		wp_localize_script( $this->plugin_name, 'TmsmAquatonicMemberareaApp', $params);
	}

	/**
	 * Register the shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'tmsm-aquatonic-memberarea', array( $this, 'shortcode') );
	}


	/**
	 * Login shortcode
	 *
	 * @since    1.0.0
	 */
	public function shortcode($atts) {
		$atts = shortcode_atts( array(
			'size' => 'normal',
			'option' => '',
		), $atts, 'tmsm-aquatonic-memberarea-calendar' );


		$action = sanitize_text_field($_GET['action']);
		switch($action){
			case 'login':
				return $this->login();
			default:
				return $this->login();
		}

	}

	/**
	 * Authenticate a user
	 *
	 * @link https://offshorly.com/news/sso-wordpress-authentication-using-external-api/
	 * @link https://ben.lobaugh.net/blog/7175/wordpress-replace-built-in-user-authentication#What_to_keep_in_mind_when_replacing_the_built-in_authentication
	 * @link https://wordpress.stackexchange.com/a/269218
	 *
	 * @param null|WP_User|WP_Error $user     User data
	 * @param string                $email User login entered
	 * @param string                $password Password entered
	 *
	 * @return null|WP_User|WP_Error User or Error
	 * @throws Exception
	 */
	public function authenticate($user, $email, $password) {

		$id_customer = null;
		$first_connection = true;

		error_log(print_r($_POST, true));

		// Login into web service if form action is tmsm-aquatonic-memberarea-login
		if ( isset($_POST['action']) && $_POST['action'] === 'tmsm-aquatonic-memberarea-login' ) {

			// Connect to API
			$api      = new ResaCours_API();
			$data     = [
				'email'    => sanitize_text_field($email),
				'password' => bin2hex( sanitize_text_field( $password ) ),
			];
			$response = $api->request( $data, 'connectioncustomer' );

			// Web service customer error
			if ( is_wp_error( $response ) ) {
				error_log('Customer authentication error');
				return $response;
			}
			// Web service customer exists
			else {
				error_log('Customer authentication success');
				$id_customer      = intval($response[0]->id_customer);
				$first_connection = $response[0]->first_connection;

				// Check if customer ID defined
				if( ! empty($id_customer)){

					// Get customer info
					$customer = $this->get_customer($id_customer);

					// Check if a user exists with same email
					$user = get_user_by('user_email', $email);

					// User exists
					if ( $user instanceof WP_User ) {
						error_log('WP user exists: use it');
					}
					else{
						error_log('WP user doesnt exist: create it');

						$userdata = array(
							'user_email' => $email,
							'user_login' => $email,
							'user_pass'  => $password,
							'first_name' => $customer->firstname,
							'last_name'  => $customer->lastname,
						);
						if(class_exists('WooCommerce')){
							$userdata['role'] = 'customer';
						}
						$user_id = wp_insert_user( $userdata ); // A new user has been created

						if ( ! is_wp_error( $user_id ) ) { // check if insert was successful
							$user = new WP_User( $user_id );
							update_user_option( $user_id, '_aquos_created', 1, false );
						}

					}

					// WP User add meta
					update_user_option( $user->ID, '_aquos_subscribercard', $customer->subscriber_card, false );
					update_user_option( $user->ID, '_aquos_id', $id_customer, false );


					// Log user
					wp_set_current_user( $user->ID, $user->user_login );
					wp_set_auth_cookie( $user->ID, true );

					do_action( 'wp_login', $user->user_login, $user );
				}
				else{
					error_log('Customer ID not defined');
				}


			}

		}

		return $user;
	}

	/**
	 * Get Customer with API
	 *
	 * @param int $id_customer
	 *
	 * @return array|mixed|WP_Error
	 * @throws Exception
	 */
	private function get_customer(int $id_customer){
		$api      = new ResaCours_API();
		$data     = [
			'id_customer'    => $id_customer,
		];
		$response = $api->request( $data, 'getcustomer' );

		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			return $response[0];
		}
	}

	/**
	 * Login Page
	 *
	 * @return string
	 * @throws Exception
	 */
	private function login(){

		$output = '';
		$error = null;
		$id_customer = null;
		$first_connection = true;

		session_start();

		// Form submitted
		if ( isset( $_POST['login'], $_POST['email'], $_POST['password'] ) ) {

			// Security OK
			if( wp_verify_nonce( $_REQUEST['tmsm_aquatonic_memberarea_nonce'], 'tmsm_aquatonic_memberarea_login' ) ) {
				$api      = new ResaCours_API();
				$data     = [
					'email'    => sanitize_email( $_POST['email'] ),
					'password' => bin2hex( sanitize_text_field( $_POST['password'] ) ),
				];
				$response = $api->request( $data, 'connectioncustomer' );

				if ( is_wp_error( $response ) ) {
					$error = $response->get_error_message();
				} else {
					$id_customer      = $response[0]->id_customer;
					$first_connection = $response[0]->first_connection;


					$_SESSION['tmsm-aquatonic-memberarea'] = ['id_customer' => $id_customer];
				}
			}
			else{
				$error = __( 'Operation not authorized', 'tmsm-aquatonic-memberarea' );
			}
		}


		print_r('_SESSION:');
		print_r($_SESSION);
		// Form
		$form = '';

		if(! empty ( $error)){
			$form .= '<div class="alert alert-danger"><p><strong>'.esc_html__('Error:', 'tmsm-aquatonic-memberarea').'</strong> '.esc_html($error).'</p></div>';
		}

		if(! empty ( $id_customer)){
			$form .= '<div class="alert alert-success"><p><strong>'.esc_html__('Customer ID:', 'tmsm-aquatonic-memberarea').'</strong> '.esc_html($id_customer).'</p></div>';
		}

		$form .= self::logged_customer().'
				<form class="tmsm-aquatonic-memberarea-login-form form-horizontal" method="post">

					<div class="form-group">
						<label for="username" class="control-label">'.esc_html__('Email', 'tmsm-aquatonic-memberarea').' <span class="required">*</span></label>
						<div class="form-input">
							<input type="text" class="form-control" name="email" id="email" autocomplete="email" value="">
						</div>
					</div>

					<div class="form-group">
						<label for="password" class="control-label">'.esc_html__('Password', 'tmsm-aquatonic-memberarea').' <span class="required">*</span></label>
						<div class="form-input">
							<span class="password-input"><input class="form-control" type="password" name="password" id="password" autocomplete="current-password"><span class="show-password-input"></span></span>
						</div>
					</div>

					<div class="form-group">
						<div class="form-actions">
							<label class="inline">
								<input class="input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever"> <span>'.esc_html__('Remember me', 'tmsm-aquatonic-memberarea').'</span>
							</label>
						</div>
					</div>

					<div class="form-group">
						<div class="form-actions">
						'.wp_nonce_field('tmsm_aquatonic_memberarea_login', 'tmsm_aquatonic_memberarea_nonce', true, false).'
							<button type="submit" class="btn btn-primary" name="login" value="'.esc_attr__('Sign-in', 'tmsm-aquatonic-memberarea').'">'.esc_html__('Sign-in', 'tmsm-aquatonic-memberarea').'</button>
						</div>
					</div>

					<div class="form-group">
						<div class="lost_password form-actions">
							<a class="btn-link" href="http://aquatonicdev.lndo.site/rennes/boutique/mon-compte/motdepasseperdu/">'.esc_html__('Forgot your password?', 'tmsm-aquatonic-memberarea').'</a>
						</div>
					</div>

					
				</form>
		';

		/*$output = '<div id="tmsm-aquatonic-memberarea-login" class="tmsm-aquatonic-memberarea-container">' . wp_login_form( [ 'echo'    => false,		                                                                                                                     'form_id' => 'tmsm-aquatonic-memberarea-login',
			] ) . '</div>';*/

		$output = '<div id="tmsm-aquatonic-memberarea-login" class="tmsm-aquatonic-memberarea-container">' . $form . '</div>';
		return $output;
	}


	/*
	 * Override Login Form bottom
	 *
	 * @param string $login_form_bottom
	 * @param array $args
	 *
	 * @return string
	 */
	public function login_form_bottom( $content, array $args): string{
		if($args['form_id'] === 'tmsm-aquatonic-memberarea-login'){
			$content .= '<input type="hidden" name="action" value="tmsm-aquatonic-memberarea-login" />';
		}
		return $content;
	}

	/**
	 * Display logged in user info
	 *
	 * @return string
	 */
	private function logged_customer() {
		$output = '';

		if(isset($_SESSION['tmsm-aquatonic-memberarea']['id_customer'])){
			$output = esc_html(sprintf(__('Logged in customer %s', 'tmsm-aquatonic-memberarea'), $_SESSION['tmsm-aquatonic-memberarea']['id_customer']));

		}
		else{
			$output = esc_html__('You are not logged in', 'tmsm-aquatonic-memberarea');
		}

		return '<div id="tmsm-aquatonic-memberarea-loggedin">'. $output.'</div>';
	}

	/**
	 * Send a response to ajax request, as JSON.
	 *
	 * @param mixed $response
	 */
	private function ajax_return( $response = true ) {
		echo json_encode( $response );
		exit;
	}

	/**
	 * Ajax check nonce security
	 */
	private function ajax_checksecurity(){

		$security = sanitize_text_field( $_REQUEST['nonce'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquatonic-memberarea-nonce-action' ) ) {
			$errors[] = __('Token security is not valid', 'tmsm-aquatonic-memberarea');
		}
		else {
		}
		if(check_ajax_referer( 'tmsm-aquatonic-memberarea-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquatonic-memberarea');
		}
		else{
		}

		if(!empty($errors)){
			wp_send_json($jsondata);
			wp_die();
		}

	}

	/**
	 * Ajax For Products
	 *
	 * @since    1.0.0
	 */
	public function ajax_realtime() {

		$this->ajax_checksecurity();
		$this->ajax_return( $this->get_realtime_data() );

	}

}
