<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Communicates with Dialog Insight API.
 */
class Dialog_Insight_API {

	const ENDPOINT           = 'https://app.mydialoginsight.com/webservices/ofc4/';
	const PLUGIN_NAME = 'tmsm-aquatonic-course-booking';

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
	 * Generates the user agent we use to pass to API request so
	 * Dialog Insight can identify our application.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	public static function get_user_agent() {
		$app_info = array(
			'name'    => 'TMSM Aquatonic Course Booking',
			'url'     => 'https://github.com/thermesmarins/tmsm-aquatonic-course-booking',
			'version'     => TMSM_AQUATONIC_COURSE_BOOKING_VERSION,
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
	 * @since 4.0.0
	 * @version 4.0.0
	 */
	public static function get_headers() {
		$user_agent = self::get_user_agent();
		$app_info   = $user_agent['application'];

		return array(
				'User-Agent'                 => $app_info['name'] . '/' . $app_info['version'] . ' (' . $app_info['url'] . ')',
				'X-DialogInsight-Client-User-Agent' => json_encode( $user_agent ),
		);
	}

	/**
	 * Get option
	 * @param string $option_name
	 *
	 * @return null
	 */
	static function get_option($option_name = null){

		$options = get_option(self::PLUGIN_NAME . '-options');

		if(!empty($option_name)){
			return $options[$option_name] ?? null;
		}
		else{
			return $options;
		}

	}

	/**
	 * Send the request to Stripe's API
	 *
	 * @since 1.0.0
	 * @param array $request
	 * @param string $api
	 * @param string $method
	 * @return stdClass|array
	 * @throws Exception
	 */
	public static function request( $request, $api = null, $method = null) {

		$headers         = self::get_headers();

		if ( $api === 'relationaltables' ) {
			$request['idTable'] = self::get_option( 'dialoginsight_relationaltableid' );
		}

		if ( $api === 'contacts' || $api === 'projects' ) {
			$request['idProject'] = self::get_option( 'dialoginsight_idproject' );
		}

		$request['AuthKey'] = [
			'idKey' => self::get_option( 'dialoginsight_idkey' ),
			'Key'   => self::get_option( 'dialoginsight_apikey' ),
		];

		//error_log( 'request after:' );
		//error_log( print_r( $request, true ) );

		$response = wp_safe_remote_post(
			self::ENDPOINT . $api . '.ashx?method=' . $method,
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
			error_log( __( 'Web service is not available', 'tmsm-aquotonic-course-booking' ) );
			throw new Exception( __( 'Web service is not available', 'tmsm-aquotonic-course-booking' ), wp_remote_retrieve_response_code( $response ) );
		}
		else{

			if ( $response_code >= 400 ) {
				error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquotonic-course-booking' ), absint( $response_code ) ) );
				throw new Exception( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquotonic-course-booking' ), absint( $response_code ) ), $response_code );

			}

			if ( is_wp_error( $response ) ) {
				error_log('Error message: '. $response->get_error_message());
				throw new Exception( 'Error message: '. $response->get_error_message(), $response_code );
			}

			// No errors, success
			if ( ! empty( $response_data->Status ) && $response_data->Status == 'true' ) {
				if ( defined( 'TMSM_AQUOS_SPA_BOOKING_DEBUG' ) && TMSM_AQUOS_SPA_BOOKING_DEBUG ) {
					error_log( 'Web service submission successful' );
				}
			}
			// Some error detected
			else{
				if ( ! empty( $response_data->ErrorCode ) && ! empty( $response_data->ErrorMessage ) ) {
					error_log( sprintf( __( 'Error code %s: %s', 'tmsm-aquotonic-course-booking' ), $response_data->ErrorCode, $response_data->ErrorMessage ) );
					throw new Exception( sprintf( __( 'Error code %s: %s', 'tmsm-aquotonic-course-booking' ), $response_data->ErrorCode, $response_data->ErrorMessage ), wp_remote_retrieve_response_code( $response ) );
				}
			}
		}

		return $response_data;
	}


}
