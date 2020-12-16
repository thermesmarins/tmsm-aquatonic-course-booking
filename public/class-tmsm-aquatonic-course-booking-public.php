<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/public
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Course_Booking_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tmsm_Aquatonic_Course_Booking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tmsm_Aquatonic_Course_Booking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-course-booking-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tmsm_Aquatonic_Course_Booking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tmsm_Aquatonic_Course_Booking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		$posts = get_posts( array(
			'post_status' => 'draft,publish',
			'page' => 1,
		) );

		$post_data = array();
		foreach ( $posts as $post ) {
			$post_data[] = array(
				'id' => $post->ID,
				'title' => array(
					'rendered' => $post->post_title,
				),
				'status' => $post->post_status,
			);
		}

		wp_enqueue_script( 'jquery-mask', plugin_dir_url( __FILE__ ) . 'js/jquery.mask.min.js', array( 'jquery' ), null, true );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-course-booking-public.js', array( 'wp-backbone', 'moment', 'jquery', 'jquery-mask', 'gform_gravityforms' ), $this->version, true );

		// Javascript localization
		$translation_array = array(
			'data' => [
				'timeslots' => [],
				'locale'   => $this->get_locale(),
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'tmsm-aquatonic-course-booking-nonce-action' ),
				'rest_url' => get_rest_url(),
				'canviewpriority' => current_user_can('edit_posts'),
				'daysrangefrom' => floor($this->get_option('hoursbefore')/24),
				'daysrangeto' => floor($this->get_option('hoursafter')/24),
				'times' => [],
			],
			'i18n' => [
				'birthdateformat' => _x( 'mm/dd/yyyy', 'birthdate date format for humans', 'tmsm-aquatonic-course-booking' ),
				'loading' => __( 'Loading', 'tmsm-aquatonic-course-booking' ),
				'notimeslot' => __( 'No time slot found', 'tmsm-aquatonic-course-booking' ),

			] ,

		);
		wp_localize_script( $this->plugin_name, 'TmsmAquatonicCourseApp', $translation_array );


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
	 * Get locale
	 */
	private function get_locale() {
		return (function_exists('pll_current_language') ? pll_current_language() : substr(get_locale(),0, 2));
	}

	/**
	 * Weekday Template
	 */
	public function template_weekday(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-course-booking-weekday">
			{{ data.date_label_firstline }} <span class="secondline">{{ data.date_label_secondline }}</span>
			<ul class="tmsm-aquatonic-course-booking-weekday-times list-unstyled" data-date="{{ data.date_computed }}" >
				<li>{{ TmsmAquatonicCourseApp.i18n.loading }}</li>
			</ul>
		</script>

		<?php
	}

	/**
	 * Time Template
	 */
	public function template_time(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-course-booking-time">
			<# if ( data.hourminutes != null) { #>
			<a class="tmsm-aquatonic-course-booking-time-button <?php echo self::button_class_default(); ?> tmsm-aquatonic-course-booking-time" href="#" data-date="{{ data.date }}" data-hour="{{ data.hour }}" data-minutes="{{ data.minutes }}" data-hourminutes="{{ data.hourminutes }}" data-priority="{{ data.priority }}">{{ data.hourminutes }} <# if ( TmsmAquatonicCourseApp.data.canviewpriority == "1" && data.priority == 1) { #> <!--*--><# } #></a> <a href="#" class="tmsm-aquatonic-course-booking-time-change-label"><?php echo __( 'Change time', 'tmsm-aquatonic-course-booking' ); ?></a>
			<# } else { #>
			{{  TmsmAquatonicCourseApp.i18n.notimeslot }}
			<# } #>

		</script>
		<?php
	}




	/**
	 * Booking Submission
	 *
	 * @param $entry
	 * @param $form
	 */
	function booking_submission( $entry, $form ) {
		global $wpdb;
		//error_log(print_r($entry, true));
		//error_log(print_r($form, true));


		$birthdate = sanitize_text_field(self::field_value_from_class('tmsm-aquatonic-course-birthdate', $form['fields'], $entry));
		$course_start = sanitize_text_field(self::field_value_from_class('tmsm-aquatonic-course-date', $form['fields'], $entry) . ' '.self::field_value_from_class('tmsm-aquatonic-course-hourminutes', $form['fields'], $entry).':00');

		error_log('field firstname: '. self::field_id_from_class('tmsm-aquatonic-course-firstname', $form['fields']));
		error_log('field lastname: '. self::field_id_from_class('tmsm-aquatonic-course-lastname', $form['fields']));
		error_log('value birthdate: '. $birthdate);
		error_log('field email: '. self::field_id_from_class('tmsm-aquatonic-course-email', $form['fields']));
		error_log('field phone: '. self::field_id_from_class('tmsm-aquatonic-course-phone', $form['fields']));
		error_log('field participants: '. self::field_id_from_class('tmsm-aquatonic-course-participants', $form['fields']));
		error_log('field date: '. self::field_id_from_class('tmsm-aquatonic-course-date', $form['fields']));
		error_log('field hourminutes: '. self::field_id_from_class('tmsm-aquatonic-course-hourminutes', $form['fields']));

		error_log('field firstname: '. self::field_value_from_class('tmsm-aquatonic-course-firstname', $form['fields'], $entry));
		error_log('field lastname: '. self::field_value_from_class('tmsm-aquatonic-course-lastname', $form['fields'], $entry));
		error_log('field email: '. self::field_value_from_class('tmsm-aquatonic-course-email', $form['fields'], $entry));
		error_log('field phone: '. self::field_value_from_class('tmsm-aquatonic-course-phone', $form['fields'], $entry));
		error_log('field participants: '. self::field_value_from_class('tmsm-aquatonic-course-participants', $form['fields'], $entry));
		error_log('field course_start: '. $course_start);
		error_log('field hourminutes: '. self::field_value_from_class('tmsm-aquatonic-course-hourminutes', $form['fields'], $entry));

		// Convert birthdate
		if(!empty($birthdate)){
			$objdate = DateTime::createFromFormat( _x( 'm/d/Y', 'birthdate date format for machines', 'tmsm-aquatonic-course-booking' ), $birthdate );
			error_log('birthdate object:');

			error_log(_x( 'mm/dd/yyyy', 'birthdate date format for humans', 'tmsm-aquatonic-course-booking' ));
			error_log(_x( 'm/d/y', 'birthdate date format for machines', 'tmsm-aquatonic-course-booking' ));
			error_log(print_r($objdate, true));
			$birthdate_computed = $objdate->format( 'Y-m-d' ) ?? null;
			error_log('birthdate_computed: '. $birthdate_computed);
		}

		// Calculate date start and end of course
		error_log('courseaverage: '.$this->get_option( 'courseaverage' ));
		$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $course_start );
		$objdate->modify( '+' . $this->get_option( 'courseaverage' ) . ' minutes' );
		$course_end = $objdate->format( 'Y-m-d H:i:s' );


		$table = $wpdb->prefix . 'aquatonic_course_booking';
		$data = array(
			'firstname' => self::field_value_from_class('tmsm-aquatonic-course-firstname', $form['fields'], $entry),
			'lastname' => self::field_value_from_class('tmsm-aquatonic-course-lastname', $form['fields'], $entry),
			'email' => self::field_value_from_class('tmsm-aquatonic-course-email', $form['fields'], $entry),
			'phone' => self::field_value_from_class('tmsm-aquatonic-course-phone', $form['fields'], $entry),
			'birthdate' => $birthdate_computed,
			'participants' => self::field_value_from_class('tmsm-aquatonic-course-participants', $form['fields'], $entry),
			'status' => 'active',
			'date_created' => date('Y-m-d H:i:s'),
			'course_start' => $course_start,
			'course_end' => $course_end,
			'author' => get_current_user_id(),
		);

		$format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			);

		$wpdb->insert($table,$data,$format);
		$my_id = $wpdb->insert_id;


	}

	/**
	 * Find the field value with a class in a field list from a Gravity Form
	 *
	 * @param $find_class
	 * @param $fields
	 * @param $entry
	 *
	 * @return string
	 */
	static function field_value_from_class($find_class, $fields, $entry){

		return rgar($entry, self::field_id_from_class($find_class, $fields));

	}

	/**
	 * Find the field id with a class in a field list from a Gravity Form
	 *
	 * @param $find_class
	 * @param $fields
	 *
	 * @return string
	 */
	static function field_id_from_class($find_class, $fields){

		foreach($fields as $field){

			$class = $field['cssClass'];
			if($class === $find_class){
				return $field['id'];

			}
			else{
				if(!empty($field['inputs'])){
					foreach($field['inputs'] as $field_input){
						$class = $field_input['name'];
						if($class === $find_class){
							return $field_input['id'];
						}
					}
				}
			}
		}
	}


	/**
	 * Button Class Default
	 *
	 * @return string
	 */
	private static function button_class_default(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'btn btn-default';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'button';
		}
		return $buttonclass;
	}

	/**
	 * Button Class Primary
	 *
	 * @return string
	 */
	private static function button_class_primary(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'btn btn-primary';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'button';
		}
		return $buttonclass;
	}


	/**
	 * Alert Class Error
	 *
	 * @return string
	 */
	private static function alert_class_error(){
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'alert alert-danger';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'alert';
		}
		return $buttonclass;
	}


	/**
	 * Ajax For Times
	 *
	 * @since    1.0.0
	 */
	public function ajax_times() {

		error_log('ajax_times');

		$this->ajax_checksecurity();
		$this->ajax_return( $this->_get_times() );
	}



	/**
	 * Ajax check nonce security
	 */
	private function ajax_checksecurity(){
		$security = sanitize_text_field( $_REQUEST['nonce'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquatonic-course-booking-nonce-action' ) ) {
			$errors[] = __('Token security is not valid', 'tmsm-aquatonic-course-booking');
			if( defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG ){
				error_log('Token security is not valid');
			}
		}
		else {
			if( defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG ){
				error_log( 'Token security is valid' );
			}
		}
		if(check_ajax_referer( 'tmsm-aquatonic-course-booking-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquatonic-course-booking');
			if( defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG ){
				error_log('Ajax referer is not valid');
			}
		}
		else{
			if( defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG ){
				error_log( 'Ajax referer is valid' );
			}
		}

		if(!empty($errors)){
			wp_send_json($jsondata);
			wp_die();
		}

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
	 * Get Times from Web Service
	 *
	 * @since    1.0.0
	 *
	 * @return array
	 */
	private function _get_times() {

		$times[] = [
			'date' => '2020-12-20',
			'hour' => '10',
			'minutes' => '10',
			'hourminutes' => '10:10',
			'priority' => 11,
		];

		return $times;
	}


}
