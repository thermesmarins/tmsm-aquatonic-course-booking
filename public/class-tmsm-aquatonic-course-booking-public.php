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

		self::_get_times();


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
			'form_fields' => [
				'date_field' => '.tmsm-aquatonic-course-date input',
				'hour_field' => '.tmsm-aquatonic-course-hourminutes .gfield_time_hour input',
				'minutes_field' => '.tmsm-aquatonic-course-hourminutes .gfield_time_minute input',
				'submit_button' => '.gform_button[type=submit]',
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
			<a class="tmsm-aquatonic-course-booking-time-button <?php echo self::button_class_default(); ?> tmsm-aquatonic-course-booking-time" href="#" data-date="{{ data.date }}" data-hour="{{ data.hour }}" data-minutes="{{ data.minutes }}" data-hourminutes="{{ data.hourminutes }}" data-priority="{{ data.priority }}" data-capacity="{{ data.capacity }}">{{ data.hourminutes }} <# if ( TmsmAquatonicCourseApp.data.canviewpriority == "1" && data.priority == 1) { #> <!--*--><# } #></a> <a href="#" class="tmsm-aquatonic-course-booking-time-change-label"><?php echo __( 'Change time', 'tmsm-aquatonic-course-booking' ); ?></a>
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
			//error_log(print_r($objdate, true));
			$birthdate_computed = $objdate->format( 'Y-m-d' ) ?? null;
			error_log('birthdate_computed: '. $birthdate_computed);
		}

		// Calculate date start and end of course
		error_log('courseaverage: '.$this->get_option( 'courseaverage' ));
		if(!empty($course_start)){
			$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $course_start );
			$objdate->modify( '+' . $this->get_option( 'courseaverage' ) . ' minutes' );
			$course_end = $objdate->format( 'Y-m-d H:i:s' );
		}

		if(!empty($course_start) && !empty($course_start)) {
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
	 * Returns TMSM Aquatonic Attendance Options
	 *
	 * @return mixed|void
	 */
	private function attendance_options(){
		return get_option('tmsm-aquatonic-attendance-options');
	}

	/**
	 * Returns Attendance Timeslots
	 *
	 * @return mixed|null
	 */
	private function capacity_periods_settings(){


		return $this->get_option('timeslots');
		/*
		$attendance_options = self::attendance_options();
		if( empty($attendance_options) ){
			return null;
		}
		if( empty($attendance_options['timeslots'] ) ){
			return null;
		}

		return $attendance_options['timeslots'];*/

	}

	/**
	 * Returns Opening Times for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array|null
	 */
	public function capacity_periods_forthedate( string $date = ''){

		$attendance_timeslots = self::capacity_periods_settings();

		if(empty($attendance_timeslots)){
			return null;
		}

		if(empty($date)){
			return null;
		}

		$date_object = DateTime::createFromFormat( 'Y-m-d', $date );
		if(empty($date_object)){
			return null;
		}
		$times = [];
		$timeslots = $attendance_timeslots.PHP_EOL;
		$timeslots_items = preg_split('/\r\n|\r|\n/', esc_attr($timeslots));
		$open = false;
		$capacity = 0;

		//print_r('$timeslots_items');
		//print_r($timeslots_items);

		//error_log('$timeslots_items');
		//error_log(print_r($timeslots_items , true));

		// First pass to list all slots
		foreach($timeslots_items as &$timeslots_item){

			$tmp_timeslots_item = $timeslots_item;
			$tmp_timeslots_item_array = explode('=', $tmp_timeslots_item);

			if ( is_array( $tmp_timeslots_item_array ) && count($tmp_timeslots_item_array) === 3 ) {
				$timeslots_item = [
					'times'    => trim( $tmp_timeslots_item_array[1] ),
					'capacity' => trim( $tmp_timeslots_item_array[2] ),
				];
				if ( (int) $tmp_timeslots_item_array[0] < 7 ) {
					$timeslots_item['daynumber'] = trim( $tmp_timeslots_item_array[0] );
				} else {
					$timeslots_item['date'] = trim( $tmp_timeslots_item_array[0] );
				}


			}
		}

		//print_r('$timeslots_items after first step');
		//print_r($timeslots_items);

		//error_log('$timeslots_items after first step');
		//error_log(print_r($timeslots_items , true));

		$timeslots_item = null;

		$date_dayoftheweek = $date_object->format( 'w' );
		$date = $date_object->format( 'Y-m-d' );

		// Second pass for slots matching date
		foreach($timeslots_items as $timeslots_key => $timeslots_item_to_parse){

			if ( isset( $timeslots_item_to_parse['date'] ) && $timeslots_item_to_parse['date'] == $date ) {
				$found_slots_for_date = true;
				foreach( explode(',', $timeslots_item_to_parse['times']) as $timeslots_times){
					$times[] = ['times' => $timeslots_times, 'capacity' => $timeslots_item_to_parse['capacity']];
				}
			}
		}
		//print_r('$times after second pass');
		//print_r( $times );

		if ( empty( $times ) ) {
			// Third pass for slots matching day of the week
			foreach($timeslots_items as $timeslots_key => $timeslots_item_to_parse){

				if ( isset( $timeslots_item_to_parse['daynumber'] ) && $timeslots_item_to_parse['daynumber'] == $date_dayoftheweek ) {
					foreach( explode(',', $timeslots_item_to_parse['times']) as $timeslots_times){
						$times[] = ['times' => $timeslots_times, 'capacity' => $timeslots_item_to_parse['capacity']];
					}
				}
			}

			//print_r('$times after third pass');
			//print_r( $times );
		}


		//error_log('$times');
		//error_log(print_r($times , true));

		return $times;

	}

	/**
	 * Returns Opening Times for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array
	 */
	public function capacity_timeslots_forthedate( string $date = ''){

		$total_capacity = [];

		$opening_periods = self::capacity_periods_forthedate( $date );
		$date_with_dash = $date;
		if ( empty( $opening_periods ) ) {
			return $total_capacity;
		}

		$averagecourse = $this->get_option('courseaverage');

		$slotsize = $this->get_option('slotsize');
		$slotminutes = 15;
		if( $slotsize == 6){
			$slotminutes = 10;
		}
		if( $slotsize == 3){
			$slotminutes = 20;
		}
		if( $slotsize == 2){
			$slotminutes = 30;
		}


		$interval = DateInterval::createFromDateString($slotminutes . ' minutes');


		//error_log('$opening_periods');
		//error_log(print_r($opening_periods, true));

		//print_r('$opening_periods');
		//print_r($opening_periods);

		// First pass to calculate start and end datetimes
		foreach($opening_periods as &$opening_period){

			$opening_details = explode('-', $opening_period['times']);
			$opening_capacity = explode('-', $opening_period['capacity']);
			$opening_start = $opening_details[0];
			$opening_end = $opening_details[1];

			$opening_period['start'] = DateTime::createFromFormat( 'Y-m-d H:i', $date_with_dash. ' '. $opening_start);
			$opening_period['end'] = DateTime::createFromFormat( 'Y-m-d H:i', $date_with_dash. ' '. $opening_end);

		}

		//print_r('$opening_periods after first pass');
		//print_r($opening_periods);

		//error_log('$opening_periods after first pass');
		//error_log(print_r($opening_periods, true));

		unset($opening_period);

		// Second pass to see if there are overlapping slots
		foreach ( $opening_periods as $opening_period ) {
			$period = new DatePeriod( $opening_period['start'], $interval, $opening_period['end'] );

			//print_r('$period');
			//print_r($period);

			//error_log('$opening_period');
			//error_log(print_r($opening_period, true));

			foreach ( $period as $slot_begin ) {
				$slot_end = clone $slot_begin;
				$slot_end->modify( '+' . $averagecourse . ' minutes' );

				//echo "\n".$slot_begin->format( "Y-m-d H:i:s" ) . ' - '.$opening_period['capacity'];
				$total_capacity[$slot_begin->format( "Y-m-d H:i:s" )] = $opening_period['capacity'];
			}


		}
		return $total_capacity;
	}

	/**
	 * Get Times from Web Service
	 *
	 * @since    1.0.0
	 *
	 * @return array
	 */
	private function _get_times() {
		global $wpdb;

		$times = [];
		$date = date('Y-m-d');
		$participants = 0;

		if(!empty($_REQUEST['date'])){
			$date                = sanitize_text_field( $_REQUEST['date'] );
		}
		$date_with_dash      = $date;

		if(!empty($_REQUEST['participants'])){
			$participants                = sanitize_text_field( $_REQUEST['participants'] );
		}

		if(!empty($date) && $participants > 0){
			error_log('$date:'.$date);
			error_log('$participants:'.$participants);

			$averagecourse = $this->get_option('courseaverage');

			$slotsize = $this->get_option('slotsize');
			$slotminutes = 15;
			if( $slotsize == 6){
				$slotminutes = 10;
			}
			if( $slotsize == 3){
				$slotminutes = 20;
			}
			if( $slotsize == 2){
				$slotminutes = 30;
			}


			$interval = DateInterval::createFromDateString($slotminutes . ' minutes');


			$capacity_forthedate_timeslots = self::capacity_timeslots_forthedate($date_with_dash);

			//usort($slots_in_opening_hours, function($a, $b) {
			//	return strcmp($a['start'], $b['start']);
			//});
			ksort($capacity_forthedate_timeslots);
			error_log('$capacity_forthedate_timeslots');
			error_log(print_r($capacity_forthedate_timeslots , true));


			// Get first and last slot of all the slots
			$first_slot = null;
			$last_slot = null;
			if(!empty(array_key_first($capacity_forthedate_timeslots))){
				$errors[] = __( 'No first slot available to calculate all the slots', 'tmsm-aquatonic-course-booking' );
				$first_slot = DateTime::createFromFormat( 'Y-m-d H:i:s', array_key_first($capacity_forthedate_timeslots));

			}
			if(!empty(array_key_last($capacity_forthedate_timeslots))){
				$errors[] = __( 'No end slot available to calculate all the slots', 'tmsm-aquatonic-course-booking' );
				$last_slot = DateTime::createFromFormat( 'Y-m-d H:i:s', array_key_last($capacity_forthedate_timeslots));
			}


			//error_log('$first_slot');
			//error_log(print_r($first_slot , true));
			//error_log('$last_slot');
			//error_log(print_r($last_slot , true));


			// Get all slots between first and last, then get participants for each one
			$uses = [];
			if(!empty($first_slot) && !empty($last_slot)){
				$period = new DatePeriod( $first_slot, $interval, $last_slot );
				foreach ( $period as $slot_begin ) {
					error_log( 'slot: '.$slot_begin->format( "Y-m-d H:i:s" ) );

					$uses[$slot_begin->format( "Y-m-d H:i:s" )] = self::get_participants_ongoing_forthetime($slot_begin);

				}

			}



			error_log('$uses');
			error_log(print_r($uses, true));

			$all_slots = $capacity_forthedate_timeslots;
			$slots_available = [];
			// Get all available slots and calculate the remaining availability with participants number during all the duration of the slot
			foreach ($all_slots as $slot_begin => $capacity ){
				$slot_begin_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $slot_begin, wp_timezone());
				$slot_end_object = clone $slot_begin_object;
				$slot_end_object->modify( '+' . $averagecourse . ' minutes' );

				$allow_begin = new DateTime('now', wp_timezone());
				$allow_begin_customer = new DateTime('now', wp_timezone());
				$allow_begin_customer->modify('+'.(60*$this->get_option('hoursbefore')) . ' minutes');
				$allow_end_customer = new DateTime('now', wp_timezone());
				$allow_end_customer->modify('+'.(60*$this->get_option('hoursafter')) . ' minutes');

				// Is a customer
				if( $this->user_has_role(wp_get_current_user(), 'customer') || ! wp_get_current_user() ) {
					if ( $slot_begin_object < $allow_begin_customer || $slot_begin_object > $allow_end_customer ) {
						continue;
					}
				}
				// Is not a customer
				else{
					if($slot_begin_object < $allow_begin ) {
						continue;
					}
				}


				$period = new DatePeriod( $slot_begin_object, $interval, $slot_end_object );

				error_log('calculate capacity for '.$slot_begin_object->format( "Y-m-d H:i:s" ));

				$min_capacity = 1000;
				foreach ( $period as $period_begin ) {
					$period_begin_uses           = $uses[ $period_begin->format( "Y-m-d H:i:s" ) ] ?? 0;
					$period_begin_total_capacity = $capacity_forthedate_timeslots[ $period_begin->format( "Y-m-d H:i:s" ) ] ?? 0;
					$min_capacity            = min( $min_capacity, $period_begin_total_capacity - $period_begin_uses );
				}
				if($participants <= $min_capacity){
					$slots_available[$slot_begin] = [
						'date' => $slot_begin_object->format('Y-m-d'),
						'hour' => $slot_begin_object->format('H'),
						'minutes' => $slot_begin_object->format('i'),
						'hourminutes' => $slot_begin_object->format('H:i'),
						'capacity' => $min_capacity,
					];
				}

				if($slots_available[$slot_begin]['capacity'] <= 0){
					unset($slots_available[$slot_begin]);
				}
			}

			error_log('$slots_available');
			error_log(print_r($slots_available , true));


			/*$times[] = [
				'date' => '2020-12-20',
				'hour' => '10',
				'minutes' => '10',
				'hourminutes' => '10:10',
				'priority' => 1,
			];
			$times[] = [
				'date' => '2020-12-19',
				'hour' => '17',
				'minutes' => '20',
				'hourminutes' => '17:20',
				'priority' => 1,
			];*/

			$times = array_values($slots_available);
		}



		if ( count( $times ) == 0) {
			$errors[] = __( 'No time slot available for this day', 'tmsm-aquatonic-course-booking' );
			$times[] = [
				'date' => $date_with_dash,
				'hour' => null,
				'minutes' => null,
				'hourminutes' => null,
				'priority' => null,
			];
		}

		return $times;
	}


	/**
	 * Get participants number with an ongoing course for the time
	 *
	 * @param DateTime $datetime
	 *
	 * @return int
	 */
	public function get_participants_ongoing_forthetime( DateTime $datetime){
		global $wpdb;

		$uses_count = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(participants) FROM
{$wpdb->prefix}aquatonic_course_booking WHERE ( status = %s OR status = %s ) AND course_start <= %s AND course_end > %s", 'active', 'arrived', $datetime->format( "Y-m-d H:i:s" ), $datetime->format( "Y-m-d H:i:s" ) ) );
		if(empty($uses_count)){
			$uses_count = 0;
		}
		return $uses_count;

	}

	/**
	 * Get participants number ending their course for the time
	 *
	 * @param DateTime $datetime
	 *
	 * @return int
	 */
	public function get_participants_ending_forthetime( DateTime $datetime){
		global $wpdb;

		$uses_count = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(participants) FROM
{$wpdb->prefix}aquatonic_course_booking WHERE ( status = %s OR status = %s ) AND course_end = %s", 'active', 'arrived', $datetime->format( "Y-m-d H:i:s" ) ) );
		if(empty($uses_count)){
			$uses_count = 0;
		}
		return $uses_count;

	}


	/**
	 * Get participants number starting their course for the time
	 *
	 * @param DateTime $datetime
	 *
	 * @return int
	 */
	public function get_participants_starting_forthetime( DateTime $datetime){
		global $wpdb;

		$uses_count = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(participants) FROM
{$wpdb->prefix}aquatonic_course_booking WHERE ( status = %s OR status = %s ) AND course_start = %s", 'active', 'arrived', $datetime->format( "Y-m-d H:i:s" ) ) );
		if(empty($uses_count)){
			$uses_count = 0;
		}
		return $uses_count;

	}

	/**
	 * Checks if a user has a role.
	 *
	 * @param int|\WP_User $user The user.
	 * @param string       $role The role.
	 * @return bool
	 */
	function user_has_role( $user, $role ) {
		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user );
		}

		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		return in_array( $role, $user->roles, true );
	}


}
