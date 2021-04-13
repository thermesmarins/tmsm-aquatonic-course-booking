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


		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {

			throw new Exception( print_r( $response, true ), wp_remote_retrieve_response_code( $response ) );
		} else {
			$response_decode = json_decode( $response['body'] );
			if ( ! empty( $response_decode->ErrorCode ) ) {
				throw new Exception( $response_decode->ErrorCode, wp_remote_retrieve_response_code( $response ) );
			}

		}

		return json_decode( $response['body'] );
	}


}
