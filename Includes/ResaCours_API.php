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

		self::$webservice_url = self::get_option('webservice_url');

	}

	/**
	 * Set Key ID.
	 * @param string $key_id
	 */
	public static function set_key_id( $key_id ) {
		self::$key_id = $key_id;
	}

	/**
	 * Set API Key.
	 * @param string $api_key
	 */
	public static function set_api_key( $api_key ) {
		self::$api_key = $api_key;
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
				'User-Agent'                 => $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')',
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
	 * @since 1.0.0
	 * @param array $request
	 * @param string $method
	 * @return string
	 * @throws Exception
	 */
	public static function request( $request, $method = null) {

		if(empty($method)){
			error_log( __( 'Method is not configured', 'tmsm-aquotonic-memberarea' ) );
			throw new Exception( __( 'Method is not configured', 'tmsm-aquotonic-memberarea' ) );
		}

		$headers         = self::get_headers();

		$request['AuthKey'] = [
			'idKey' => self::get_option( 'dialoginsight_idkey' ),
			'Key'   => self::get_option( 'dialoginsight_apikey' ),
		];

		//error_log( 'request after:' );
		//error_log( print_r( $request, true ) );

		$response = wp_safe_remote_post(
			self::$webservice_url . '/' . $method,
			//['data' => $request]
			array(
				'headers' => $headers,
				'body'    => json_encode($request),
				'timeout' => 70,
			)
		);

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_data = json_decode( wp_remote_retrieve_body( $response ) );

		if(empty($response)){
			error_log( __( 'Web service is not available', 'tmsm-aquotonic-memberarea' ) );
			throw new Exception( __( 'Web service is not available', 'tmsm-aquotonic-memberarea' ), wp_remote_retrieve_response_code( $response ) );
		}
		else{

			if ( $response_code >= 400 ) {
				error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquotonic-memberarea' ), absint( $response_code ) ) );
				throw new Exception( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquotonic-memberarea' ), absint( $response_code ) ), $response_code );

			}

			if ( is_wp_error( $response ) ) {
				error_log('Error message: '. $response->get_error_message());
				throw new Exception( 'Error message: '. $response->get_error_message(), $response_code );
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
					error_log( sprintf( __( 'Error code %s', 'tmsm-aquotonic-memberarea' ), $response_data->error) );
					throw new Exception( sprintf( __( 'Error code %s', 'tmsm-aquotonic-memberarea' ), $response_data->error ), wp_remote_retrieve_response_code( $response ) );
				}
			}
		}

		return $response_data;
	}


}
