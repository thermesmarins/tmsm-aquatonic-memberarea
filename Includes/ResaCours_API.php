<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Communicates with ResaCours API.
 */
class ResaCours_API {

	/**
	 * Web Service URL.
	 * @var string
	 */
	private static $webservice_url = '';

	/**
	 * Site ID.
	 * @var string
	 */
	private static $site_id = '';

	/**
	 * Key ID.
	 * @var string
	 */
	private static $key_id = '';

	/**
	 * API Key.
	 * @var string
	 */
	private static $api_key = '';



	public function __construct() {

		self::$webservice_url = self::get_option('webserviceurl');
		self::$site_id = intval(self::get_option('siteid'));

	}

	/**
	 * Generates the user agent we use to pass to API request
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public static function get_user_agent() {
		$app_info = array(
			'name'    => 'TMSM Aquatonic Member Area',
			'url'     => 'https://github.com/thermesmarins/tmsm-aquatonic-memberarea',
			'version'     => TMSM_AQUATONIC_MEMBERAREA_VERSION,
		);

		return array(
			'lang'         => 'php',
			'lang_version' => phpversion(),
			'publisher'    => 'thermesmarins',
			'uname'        => php_uname(),
			'application'  => $app_info,
		);
	}

	/**
	 * Generates the headers to pass to API request.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public static function get_headers() {
		$user_agent = self::get_user_agent();
		$app_info   = $user_agent['application'];

		return array(
			'User-Agent'   => $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')',
			'Content-Type' => 'application/json; charset=utf-8',
		);
	}

	/**
	 * Get option
	 *
	 * @param string $option_name
	 *
	 * @return null
	 */
	static function get_option($option_name = null){

		$options = get_option('tmsm-aquatonic-memberarea-options');

		if(!empty($option_name)){
			return $options[$option_name] ?? null;
		}
		else{
			return $options;
		}

	}

	/**
	 * Send the request to ResaCours API
	 *
	 * @param array $data
	 * @param string $method
	 *
	 * @return array|WP_Error
	 * @since 1.0.0
	 */
	public function request( $data, $method = null) {

		if(empty($method)){
			error_log( __( 'Method is not configured', 'tmsm-aquotonic-memberarea' ) );
			throw new Exception( __( 'Method is not configured', 'tmsm-aquotonic-memberarea' ) );
		}

		$data['id_site'] = self::$site_id;

		error_log( '$data:' );
		error_log( print_r( $data, true ) );
		error_log( 'json_encode($data):' );
		error_log( json_encode($data) );

		$response = wp_safe_remote_post(
			self::$webservice_url . '/' . $method,
			array(
				'headers' => self::get_headers(),
				'body'    => json_encode($data),
				'timeout' => 10,
			)
		);

		error_log( '$response:' );
		error_log( print_r( $response, true ) );

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_data = json_decode( wp_remote_retrieve_body( $response ) );

		error_log( '$response_code:' );
		error_log( $response_code );

		if(empty($response)){
			error_log( __( 'Web service is not available', 'tmsm-aquotonic-memberarea' ) );
			return new WP_Error( 'webservice_unavailable', __( 'Web service is not available', 'tmsm-aquotonic-memberarea' ) );
		}
		else{

			if ( is_wp_error( $response ) ) {
				error_log( 'Error message: ' . $response->get_error_message() );
				return $response;
			}

			if ( $response_code !== 200 ) {
				error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquotonic-memberarea' ), absint( $response_code ) ) );
				return new WP_Error( 'unexpected_http_response_code', sprintf( __( 'Web service error: delivery URL returned response code %s', 'tmsm-aquotonic-memberarea' ), absint( $response_code ) ) );
			}

			// No errors, success
			if ( ! empty( $response_data->status ) && $response_data->status == 'true' ) {
				if ( defined( 'TMSM_AQUATONIC_MEMBERAREA_DEBUG' ) && TMSM_AQUATONIC_MEMBERAREA_DEBUG ) {
					error_log( 'Web service submission successful' );
				}
			}
			// Some error detected
			else{
				if ( ! empty( $response_data->error ) ) {
					error_log( sprintf( __( 'Web service error message: %s', 'tmsm-aquotonic-memberarea' ), $response_data->error ) );
					return new WP_Error( 'webservice_error', sprintf( __( 'Web service error message: %s', 'tmsm-aquotonic-memberarea' ), $response_data->error ) );
				}
			}
		}

		return $response_data->data;
	}


}
