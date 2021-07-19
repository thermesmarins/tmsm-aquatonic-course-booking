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
	 * Tmsm_Aquatonic_Course_Booking_Public constructor.
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param $plugin_name
	 * @param $version
	 *
	 * @throws Exception
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-course-booking-public'.(defined( 'TMSM_AQUATONIC_COURSE_BOOKING_LOCAL' ) && TMSM_AQUATONIC_COURSE_BOOKING_LOCAL === true  ? '' : '.min').'.js', array( 'wp-backbone', 'moment', 'jquery', 'gform_gravityforms', 'wp-i18n' ), $this->version, true );

		wp_dequeue_script('gform_masked_input');
		wp_deregister_script('gform_masked_input');

		$daysrangefrom = floor($this->get_option('hoursbefore')/24);

		// Blocked Before this Date
		if(!empty($this->get_option('blockedbeforedate'))){
			$objdate_blockedbeforedate = DateTime::createFromFormat( 'Y-m-d', $this->get_option('blockedbeforedate') );
			$now = new Datetime();
			if($objdate_blockedbeforedate > $now){
				$interval = $now->diff($objdate_blockedbeforedate);
				$daysrangefrom =  $interval->format('%a');
			}
		}

		// Javascript localization
		$translation_array = array(
			'data' => [
				'timeslots' => [],
				'locale'   => $this->get_locale(),
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'tmsm-aquatonic-course-booking-nonce-action' ),
				'rest_url' => get_rest_url(),
				'canviewpriority' => current_user_can('edit_posts'),
				'daysrangefrom' => $daysrangefrom,
				'daysrangeto' => floor($this->get_option('hoursafter')/24),
				'times' => [],
			],
			'form_fields' => [
				'step' => 1,
				'date_field' => '.tmsm-aquatonic-course-date input',
				'summary_field' => '.tmsm-aquatonic-course-summary',
				'hour_field' => '.tmsm-aquatonic-course-hourminutes .gfield_time_hour input',
				'minutes_field' => '.tmsm-aquatonic-course-hourminutes .gfield_time_minute input',
				'submit_button' => '.gform_button[type=submit]',
			],
			'i18n' => [
				'birthdateformatdatepicker' => _x( 'mm/dd/yyyy', 'birthdate date format for datepicker', 'tmsm-aquatonic-course-booking' ),
				'birthdateformat' => _x( 'mm/dd/yyyy', 'birthdate date format for humans', 'tmsm-aquatonic-course-booking' ),
				'loading' => __( 'Loading', 'tmsm-aquatonic-course-booking' ),
				'notimeslot' => __( 'No time slot found', 'tmsm-aquatonic-course-booking' ),
				'closed' => __( 'Closed', 'tmsm-aquatonic-course-booking' ),
				'pickatimeslot' => __( 'Pick a time slot', 'tmsm-aquatonic-course-booking' ),
				'summary' => __( 'Course for %s participant(s) on %s at %s:%s', 'tmsm-aquatonic-course-booking' ),
				'summarymomentdateformat' => __( 'MMMM DD, YYYY', 'tmsm-aquatonic-course-booking' ),

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
	public function template_weekday_select(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-course-booking-weekday">
			{{ data.date_label_firstline }} <span class="secondline">{{ data.date_label_secondline }}</span>
			<select class="tmsm-aquatonic-course-booking-weekday-times list-unstyled" data-date="{{ data.date_computed }}" >
				<option>{{ TmsmAquatonicCourseApp.i18n.loading }}</option>
			</select>
			<span class="glyphicon glyphicon-refresh glyphicon-spin"></span>
		</script>

		<?php
	}

	/**
	 * Weekday Template
	 */
	public function template_weekday_list(){
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
	public function template_time_select(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-course-booking-time">
			<# if ( data.hourminutes != null) { #>
				{{ data.hourminutes }}
			<# } else { #>
				{{ data.message }}
			<# } #>

		</script>
		<?php
	}

	/**
	 * Time Template
	 */
	public function template_time_list(){
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
	 * Fired after an entry is created
	 *
	 * @param array $entry The Entry object
	 * @param array $form The Form object
	 */
	function gform_entry_post_save_booking( $entry, $form ) {
		//error_log('gform_entry_post_save_booking');

		if(!empty($entry)){
			$entry_id = $entry['id'];
			$booking_token = self::gform_entry_generate_token( $entry_id );
		}
	}

	/**
	 * Booking Submission
	 *
	 * @param $entry
	 * @param $form
	 *
	 * @throws Exception
	 */
	function gform_entry_created( $entry, $form ) {
		global $wpdb;

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log('gform_entry_created ');
		}

		$form_add_id = $this->get_option('gform_add_id');
		if(!empty($form_add_id)){

			if($form['id'] == $form_add_id){


				$entry_id = $entry['id'];

				// Get token
				$token = null;
				if(!empty($entry_id)){
					$token = self::gform_entry_generate_token( $entry_id );
				}

				// Get entry data
				$lastname     = self::field_value_from_class( 'tmsm-aquatonic-course-lastname', $form['fields'], $entry );
				$firstname    = self::field_value_from_class( 'tmsm-aquatonic-course-firstname', $form['fields'], $entry );
				$email        = self::field_value_from_class( 'tmsm-aquatonic-course-email', $form['fields'], $entry );
				$phone        = self::field_value_from_class( 'tmsm-aquatonic-course-phone', $form['fields'], $entry );
				$participants = self::field_value_from_class( 'tmsm-aquatonic-course-participants', $form['fields'], $entry );
				$date         = self::field_value_from_class( 'tmsm-aquatonic-course-date', $form['fields'], $entry );
				$hourminutes  = self::field_value_from_class( 'tmsm-aquatonic-course-hourminutes', $form['fields'], $entry );
				$title        = self::field_value_from_class( 'tmsm-aquatonic-course-title', $form['fields'], $entry );

				$birthdate_computed = null;
				$birthdate          = sanitize_text_field( self::field_value_from_class( 'tmsm-aquatonic-course-birthdate', $form['fields'], $entry ) );
				$course_start       = sanitize_text_field( $date . ' ' . $hourminutes . ':00' );


				/*error_log( 'field firstname value: ' . $firstname );
				error_log( 'field lastname value: ' . $lastname );
				error_log( 'value birthdate value: ' . $birthdate );
				error_log( 'field email value: ' . $email );
				error_log( 'field phone value: ' . $phone );
				error_log( 'field participants value: ' . $participants );
				error_log( 'field date value: ' . $date );
				error_log( 'field hourminutes value: ' . $hourminutes );
				error_log( 'field course_start value: ' . $course_start );
				error_log( 'token: ' . $token );*/



				// Convert birthdate
				if(!empty($birthdate)){
					$objdate = DateTime::createFromFormat( 'Y-m-d', $birthdate );
					//error_log('birthdate object:');
					//error_log(_x( 'mm/dd/yyyy', 'birthdate date format for humans', 'tmsm-aquatonic-course-booking' ));
					//error_log(_x( 'm/d/y', 'birthdate date format for machines', 'tmsm-aquatonic-course-booking' ));
					$birthdate_computed = $objdate->format( 'Y-m-d' ) ?? null;
					//error_log('birthdate_computed: '. $birthdate_computed);
				}

				// Calculate date start and end of course
				//error_log('courseaverage: '.$this->get_option( 'courseaverage' ));
				if(!empty($course_start)){
					$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $course_start );
					$objdate->modify( '+' . $this->get_option( 'courseaverage' ) . ' minutes' );
					$course_end = $objdate->format( 'Y-m-d H:i:s' );
				}

				$now = new DateTime('now', wp_timezone());

				$barcode = '';
				if(!empty($entry_id)){
					$barcode = self::gform_entry_generate_barcode( $lastname, $entry_id );
				}

				// Format data
				if ( ! empty( $course_start ) && ! empty( $course_start ) ) {
					$table = $wpdb->prefix . 'aquatonic_course_booking';
					$data = array(
						'firstname'    => $firstname,
						'lastname'     => $lastname,
						'email'        => $email,
						'phone'        => $phone,
						'birthdate'    => $birthdate_computed,
						'participants' => $participants,
						'status'       => 'active',
						'date_created' => $now->format( 'Y-m-d H:i:s' ),
						'course_start' => $course_start,
						'course_end'   => $course_end,
						'author'       => get_current_user_id(),
						'token'        => $token,
						'barcode'      => $barcode,
						'title'        => $title,
					);

					if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
						error_log(print_r($data, true));
					}

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
						'%s',
						'%s',
						'%d',
					);

					// Insert data into custom table
					$result_insert = $wpdb->insert( $table, $data, $format );
					if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
						error_log('Booking inserted result: ' . $result_insert);
					}

					// Add contact to Dialog Insight
					$contact            = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Contact();
					$contact->email     = $data['email'];
					$contact->firstname = $data['firstname'];
					$contact->lastname  = $data['lastname'];
					$contact->birthdate = $birthdate_computed;
					$contact->phone     = $data['phone'];
					$contact->title     = $data['title'];

					if($contact->add()){
						// Add booking to Dialog Insight
						$booking               = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Booking();
						$booking->email        = $data['email'];

						$booking->participants = $data['participants'];
						$booking->status       = $data['status'];
						$booking->token        = $data['token'];
						$booking->date_created = $data['date_created'];
						$booking->course_start = $data['course_start'];
						$booking->course_end   = $data['course_end'];
						//$booking->source = substr( get_option( 'blogname' ), 0, 25 );
						$booking->source = $this->get_option('dialoginsight_sourcecode');;
						$booking->add();

					}



				}
			}
		}





	}



	/**
	 * Aquos: generate signature
	 *
	 * @param string $payload
	 *
	 * @return string
	 */
	private function aquos_generate_signature( $payload ) {
		$hash_algo = 'sha256';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( hash_hmac( $hash_algo, $payload, wp_specialchars_decode( $this->aquos_secret(), ENT_QUOTES ), true ) );
	}

	/**
	 * Aquos: returns secret
	 *
	 * @return string
	 */
	private function aquos_secret() {

		$secret = $this->get_option('aquos_secret');

		return $secret;
	}

	/**
	 * Allow the text to be filtered so custom merge tags can be replaced.
	 *
	 * @param $text
	 * @param $form
	 * @param $entry
	 * @param $url_encode
	 * @param $esc_html
	 * @param $nl2br
	 * @param $format
	 *
	 * @return string
	 * @throws \Picqer\Barcode\Exceptions\BarcodeException
	 */
	public function gform_replace_merge_tags_booking( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ){

		$form_add_id = $this->get_option('gform_add_id');
		$form_cancel_id = $this->get_option('gform_cancel_id');

		if(is_array($entry)){
			if(isset($entry['entry_id'])){
				$entry_id = $entry['entry_id'];
			}
			elseif(isset($entry['id'])){
				$entry_id = $entry['id'];
			}
		}
		else{
			$entry_id = $entry;
		}

		if(!empty($form_add_id) && !empty($form_cancel_id) && !empty( $entry ) ) {
			if($form['id'] == $form_add_id || $form['id'] == $form_cancel_id ){
				$token    = self::gform_entry_generate_token( $entry_id );
				if( !empty($token)){
					$booking = self::find_booking_with_token($token);

					if(!empty($booking)){
						$custom_merge_tag_date = '{booking_date}';
						if ( strpos( $text, $custom_merge_tag_date ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$booking_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());
							$date = wp_date( get_option('date_format'), $booking_start_object->getTimestamp() );
							$text     = str_replace( $custom_merge_tag_date, $date, $text );
						}

						$custom_merge_tag_hourminutes = '{booking_hourminutes}';
						if ( strpos( $text, $custom_merge_tag_hourminutes ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$booking_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());
							$hourminutes = wp_date( get_option('time_format'), $booking_start_object->getTimestamp() );
							$text     = str_replace( $custom_merge_tag_hourminutes, $hourminutes, $text );
						}

						$custom_merge_tag_participants = '{booking_participants}';
						if ( strpos( $text, $custom_merge_tag_participants ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$text     = str_replace( $custom_merge_tag_participants, $booking['participants'], $text );
						}

						$custom_merge_tag_token = '{booking_token}';
						if ( strpos( $text, $custom_merge_tag_token ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$text     = str_replace( $custom_merge_tag_token, urlencode( $token ), $text );
						}

						$custom_merge_tag_cancel_url = '{booking_cancel_url}';
						if ( strpos( $text, $custom_merge_tag_cancel_url ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$cancel_url = '';
							if ( ! empty( $token ) ) {
								$cancel_url = self::cancel_url( $token );
							}
							$text = str_replace( $custom_merge_tag_cancel_url, $cancel_url, $text );
						}

						$custom_merge_tag_barcode = '{booking_barcode_number}';
						$custom_merge_tag_barcode_image = '{booking_barcode_image}';
						if ( strpos( $text, $custom_merge_tag_barcode ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$lastname = $booking['lastname'];
							$barcode  = self::gform_entry_generate_barcode( $lastname, $entry_id );
							if ( ! empty( $barcode ) ) {
								$generator     = new Picqer\Barcode\BarcodeGeneratorPNG();
								$barcode_url = self::barcode_url($barcode);

								if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_LOCAL' ) && TMSM_AQUATONIC_COURSE_BOOKING_LOCAL === true ) {
									$barcode_url = 'https://www.aquatonic.fr/barcode.jpg';
								}

								$text          = str_replace( $custom_merge_tag_barcode, $barcode, $text );
								$text          = str_replace( $custom_merge_tag_barcode_image, $barcode_url, $text );
							}
						}

						$custom_merge_tag_site_logo = '{site_logo}';
						if ( strpos( $text, $custom_merge_tag_site_logo ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$text = str_replace( $custom_merge_tag_site_logo, get_bloginfo( 'logo' ), $text );
						}

						$custom_merge_tag_barcode_logo = '{booking_barcode_logo}';
						if ( strpos( $text, $custom_merge_tag_barcode_logo ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$barcode_logo_url = plugins_url( 'public/img/barcode-logo.png', dirname( __FILE__ ) );

							if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_LOCAL' ) && TMSM_AQUATONIC_COURSE_BOOKING_LOCAL === true ) {
								$barcode_logo_url = 'https://www.aquatonic.fr/logo-aquatonic-round-white.png';
							}

							$text = str_replace( $custom_merge_tag_barcode_logo, $barcode_logo_url, $text );
						}

						$custom_merge_tag_site_name = '{site_name}';
						if ( strpos( $text, $custom_merge_tag_site_name ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {
							$text = str_replace( $custom_merge_tag_site_name, get_bloginfo( 'name' ), $text );
						}

						$custom_merge_tag_place_name = '{place_name}';
						if ( strpos( $text, $custom_merge_tag_place_name ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {

							$place_name = get_bloginfo( 'name' );
							if ( class_exists( 'RankMath\Helper' ) ) {
								$place_name = RankMath\Helper::get_settings( 'titles.knowledgegraph_name' );
							}

							$text = str_replace( $custom_merge_tag_place_name, $place_name, $text );
						}

						$custom_merge_tag_block = '{booking_barcode_block}';
						if ( strpos( $text, $custom_merge_tag_block ) !== false && ! empty( $entry_id ) && ! empty( $form ) ) {

							//$barcode_background_pixel_url = plugins_url( 'public/img/whitepixel.gif', dirname( __FILE__ ) );
							$barcode_background_pixel_url = 'https://via.placeholder.com/1.png/fff/fff';

							$block = '<div id="booking_barcode_block" style="background:black;padding:10px 20px;border-radius:10px;text-align:center;max-width:400px; color:white;">
<div style="display:inline-block;height:80px; width:80px;"><img style="max-width:100%; height:auto;" src="{booking_barcode_logo}" /></div>
<div style="margin:5px 0; color:white;">'.__('{place_name}<br>Aquatonic Course on {booking_date} at {booking_hourminutes}<br>{booking_participants} participant(s)', 'tmsm-aquatonic-course-booking').'</div><div style="background-color:white;background-image:url(\''. $barcode_background_pixel_url .'\');background-repeat:repeat;padding:10px 20px;border-radius:10px;"><img style="background:white; max-width:100%;height:80px;display:block" src="{booking_barcode_image}" /></div><span style="color:white;display:block;">{booking_barcode_number}</span></div><br>
							<a href="{booking_cancel_url}" class="hide">'.__('If you can\'t see the barcode, please access this page', 'tmsm-aquatonic-course-booking').'</a>';

							$block = apply_filters( 'gform_replace_merge_tags', $block, $form, $entry, $url_encode, $esc_html, $nl2br, $format );

							$text = str_replace( $custom_merge_tag_block, $block, $text );
						}

					}

				}

			}

		}

		return $text;
	}


	/**
	 * Get booking cancel page URL
	 *
	 * @param string $token
	 *
	 * @return string
	 */
	private function cancel_url( string $token ) {

		$cancel_url  = '';
		$cancel_page = $this->get_option( 'page_cancel_id' );
		if ( ! empty( $cancel_page ) ) {

			$cancel_url = get_permalink( $cancel_page ) . '?booking_token=' . urlencode( $token );

		}

		return $cancel_url;
	}

	/**
	 * Return Barcode URL
	 *
	 * @param string $barcode
	 *
	 * @return string
	 */
	private function barcode_url(string $barcode){

		$barcode_url = admin_url( 'admin-ajax.php' ) . '?action=tmsm-aquatonic-course-booking-generate-barcode&barcode='.$barcode;

		return $barcode_url;
	}

	/**
	 * Generate token for Gravity Forms entry
	 *
	 * @param int $entry_id
	 *
	 * @return string
	 */
	private function gform_entry_generate_token( int $entry_id ){

		//error_log('gform_entry_generate_token');
		// Check if token exists for entry
		$token = gform_get_meta( $entry_id, '_booking_token' );

		// Create token for entry if token doesn't exist
		if(empty($token)){
			$token = $entry_id.'-'. wp_generate_password(24, false, false);
			//error_log('gform_update_meta _booking_token: '.$token);
			gform_update_meta( $entry_id, '_booking_token', $token );
		}
		else{
			//error_log('token found : '. $token);
		}

		return $token;
	}

	/**
	 * Generate barcode for Gravity Forms entry
	 * Returns a barcode with format: R-XXXXXXXXXXX-00000000 (21 characters)
	 *
	 * @param string $lastname
	 * @param int $entry_id
	 *
	 * @return string
	 */
	private function gform_entry_generate_barcode( string $lastname, int $entry_id ){
		global $wpdb;

		// Check if barcode exists for entry
		$barcode = gform_get_meta( $entry_id, '_booking_barcode' );

		// Create barcode for entry if barcode doesn't exist
		if(empty($barcode)){
			//error_log('gform_update_meta _booking_barcode');
			$barcode = '';
			$barcode .= 'R-';
			$barcode .= str_pad(substr(strtoupper(sanitize_title($lastname)), 0, 10), 10, "X", STR_PAD_RIGHT);
			$next_id = 1;
			$table_status = $wpdb->get_row("SHOW TABLE STATUS LIKE '".$wpdb->prefix . "aquatonic_course_booking". "'");
			if ( $table_status ) {
				$next_id += $table_status->Auto_increment;
			}
			$barcode .= '-' . str_pad($next_id, 8, '0', STR_PAD_LEFT);
			gform_update_meta( $entry_id, '_booking_barcode', $barcode );
		}

		return $barcode;
	}

	/**
	 * Generate directly the image when admin ajax is called
	 *
	 *
	 * @throws \Picqer\Barcode\Exceptions\BarcodeException
	 */
	public function generate_barcode_image(){

		$barcode = sanitize_text_field( $_REQUEST['barcode'] );

		$generator     = new Picqer\Barcode\BarcodeGeneratorJPG();
		if( empty($barcode)){
			die(__('Barcode missing', 'tmsm-aquatonic-course-booking'));
		}

		try{
			$image = $generator->getBarcode( $barcode, $generator::TYPE_CODE_128_A, 3, 80 );

			//nocache_headers();
			header( "Content-type: image/jpg;" );
			header( "Content-Length: " . strlen( $image ) );

			echo $image;
		}
		catch (\Picqer\Barcode\Exceptions\BarcodeException $exception) {

			die($exception->getMessage());
		}

		die();
	}

	/**
	 * Gravity Forms: Pre Render Form Booking
	 *
	 * @param array $form
	 *
	 * @return array
	 */
	public function gform_pre_render_cancel($form){

		if($form['cssClass'] === 'tmsm-aquatonic-course-form-cancel'){

			$field_token = null;
			$field_summary = null;
			foreach($form['fields'] as $field){
				if($field->inputName === 'booking_token'){
					$field_token = $field;
				}
				if( strpos($field['cssClass'], 'tmsm-aquatonic-course-summary') !== false ){
					$field_summary = $field;
				}


			}

			if(!empty($field_token) && !empty($field_summary) ){
				$token = ( rgget( $field_token->inputName ) );
				$field_summary->content = '';
				$content_summary = null;
				$content_barcode = null;
				if(!empty($token)){
					$booking = self::find_booking_with_token($token);

					$entry = self::find_entry_with_token( $token );

					if(!empty($booking)){


						$booking_status = $booking['status'];
						$booking_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());
						$booking_start = wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option('date_format'), get_option('time_format') ) , $booking_start_object->getTimestamp() );
						$booking_participants = $booking['participants'];
						if($booking_status === 'cancelled'){
							$content_summary = __( 'This booking was already cancelled', 'tmsm-aquatonic-course-booking' );
						}
						else{
							//$content_summary = sprintf(__('Do you want to cancel the following booking? Booking on %s for %d participants', 'tmsm-aquatonic-course-booking'), sanitize_text_field($booking_start), sanitize_text_field($booking_participants) );
							$content_summary = __( 'Do you want to cancel the following booking?', 'tmsm-aquatonic-course-booking' );



						}

						$content_barcode = '{booking_barcode_block}';

						$content_barcode = apply_filters( 'gform_replace_merge_tags', $content_barcode, $form, $entry, false, false, false, null );


					}
					else{
						$content_summary = __( 'This booking was not found', 'tmsm-aquatonic-course-booking' );
					}
				}
				else{
					$content_summary = __( 'The booking token was not found', 'tmsm-aquatonic-course-booking' );
				}
				$field_summary->content .= $content_summary;
				$form['description'] .= $content_barcode;

			}
		}

		return $form;
	}

	/**
	 * Gravity Forms: Add date validation, compare year of birth to current year
	 *
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param $field
	 *
	 * @return mixed
	 */
	function gform_field_validation_birthdate( $result, $value, $form, $field ) {

		if ( $result['is_valid'] && $field->get_input_type() == 'date' && $field->cssClass === 'tmsm-aquatonic-course-birthdate') {
			$date = GFCommon::parse_date( $value, $field->dateFormat );

			if ( ! GFCommon::is_empty_array( $date ) && checkdate( $date['month'], $date['day'], $date['year'] ) ) {
				if($date['year'] < (date('Y') - 150 ) || $date['year'] > (date('Y') - 10 ) ) {
					$result['is_valid'] = false;
					$result['message'] = __( 'Year of birth is invalid', 'tmsm-aquatonic-course-booking' );
				}
			}
		}

		return $result;
	}


	/**
	 * Booking Cancellation
	 *
	 * @param $entry
	 * @param $form
	 *
	 * @throws Exception
	 */
	function gform_after_submission_cancel( $entry, $form ) {
		global $wpdb;

		//error_log('gform_after_submission_cancel');
		//error_log(print_r($entry, true));
		//error_log(print_r($form, true));

		$entry_id = $entry['id'];

		// Get entry data
		$token = sanitize_text_field( self::field_value_from_inputname( 'booking_token', $form['fields'], $entry ) );

		$table = $wpdb->prefix . 'aquatonic_course_booking';

		$data = [
			'status' => 'cancelled',
		];

		$format = [
			'%s',
		];

		$where = [
			'token' => $token
		];

		// Update data into custom table
		$wpdb->update( $table, $data, $where, $format );

		$data = [
			'token'  => $token,
			'status' => 'cancelled',
		];


		// Update booking in Dialog Insight
		$booking = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Booking();
		$booking->token = $token;
		$booking->status = 'cancelled';
		$booking->update();

	}


	/**
	 * Gravity Forms notification for booking with markup data
	 *
	 * @param array $notification
	 * @param array $form
	 * @param array $entry
	 *
	 * @return array
	 */
	public function gform_notification_booking(  $notification, $form, $entry ){

		//error_log('gform_notification_booking');

		$notification['message'] .= '';

		// Prepare data for markup
		$image           = null;
		$address         = null;
		$contact_page_id = null;
		$cancel_page_id  = get_permalink( $this->get_option( 'page_cancel_id' ) );
		$shop_name       = get_bloginfo( 'name' );
		$shop_url        = home_url();

		if ( class_exists( 'WPSEO_Options' ) && ! empty( WPSEO_Options::get( 'company_logo' ) ) ) {
			$image = WPSEO_Options::get( 'company_logo' );
		}
		if ( class_exists( 'RankMath\Helper' ) ) {
			$image           = RankMath\Helper::get_settings( 'titles.knowledgegraph_logo' );
			$shop_name       = RankMath\Helper::get_settings( 'titles.knowledgegraph_name' );
			$address         = RankMath\Helper::get_settings( 'titles.local_address' );
			$contact_page_id = RankMath\Helper::get_settings( 'titles.local_seo_contact_page' );
		}

		$entry_id  = $entry['id'];

		$token     = self::gform_entry_generate_token( $entry_id );
		if(! empty($token)){
			$booking = self::find_booking_with_token($token);

			//error_log('find_booking_with_token:');
			//error_log(print_r($booking, true));
			$lastname  = $booking['lastname'];
			$firstname  = $booking['firstname'];
			$participants  = $booking['participants'];
			$course_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());
			$barcode   = self::gform_entry_generate_barcode( $lastname, $entry_id );

			$date_for_humans = wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option( 'date_format' ),
				get_option( 'time_format' ) ), $course_start_object->getTimestamp() );

			// Building schema markup
			$markup = array();

			// Generate markup for every Event/Appointment
			$markup[] = array(
				'@context'          => 'http://schema.org',
				'@type'             => 'EventReservation',
				'reservationNumber' => $barcode,
				'reservationStatus' => 'http://schema.org/Confirmed',
				'underName'         => [
					'@type' => 'Person',
					'name'  => sanitize_text_field($firstname) . ' ' . sanitize_text_field($lastname),
				],
				'modifiedTime' => date(DATE_ATOM, time()),
				//'modifyReservationUrl' => $contact_page_id ? get_permalink($contact_page_id) : '',
				'modifyReservationUrl' => self::cancel_url($token) ?? get_permalink($contact_page_id),
				'cancelReservationUrl' => self::cancel_url($token) ?? get_permalink($contact_page_id),
				'reservationFor'    => [
					'@type'     => 'Event',
					'name'      => sprintf( __('Aquatonic Course on %s for %d participants', 'tmsm-aquatonic-course-booking'), $date_for_humans, $participants),
					'performer' => [
						'@type' => 'Organization',
						'name'  => $shop_name,
						//'image' => $image ?? '',
						//'image' => 'https://www.aquatonic.fr/nantes/wp-content/uploads/sites/8/2010/08/aquatonic-nantes-1.jpg',
						'image' => 'https://mk0aquatonicxmkh2brf.kinstacdn.com/wp-content/uploads/sites/6/2017/08/aquatonic-rennes-1.jpg',
						//'image' => 'https://mk0aquatonicxmkh2brf.kinstacdn.com/wp-content/uploads/sites/9/2012/10/parcours-aquatonic-montevrain.png',
						//https://www.aquatonic.fr/nantes/wp-content/uploads/sites/8/2017/11/logo_aquatonic-nantes-600-300.png
						//https://www.aquatonic.fr/rennes/wp-content/uploads/sites/6/2017/11/logo_aquatonic-rennes-600-300.png
						//https://www.aquatonic.fr/paris/wp-content/uploads/sites/9/2017/11/logo_aquatonic-paris-600-300.png
					],
					'startDate' => $course_start_object->format( 'Y-m-d\TH:i:s' ),
					'location'  => [
						'@type'   => 'Place',
						'name'    => $shop_name,
						'address' => [
							'@type'           => 'PostalAddress',
							'streetAddress'   => ( $address ? $address['streetAddress'] : '' ),
							'addressLocality' => ( $address ? $address['addressLocality'] : '' ),
							'addressRegion'   => ( $address ? $address['addressRegion'] : '' ),
							'postalCode'      => ( $address ? $address['postalCode'] : '' ),
							'addressCountry'  => ( $address ? $address['addressCountry'] : '' ),
						],
					],
				],
				'ticketToken' => 'barcode128:' . $barcode,
				'ticketNumber' => $barcode,
				'numSeats'     => $participants,
			);
		}
		else{
			//error_log('token is empty');
		}


		if ( $markup ) {
			$notification['message'] .= '<script type="application/ld+json">' .  wp_json_encode( $markup ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		//error_log($notification['message'] );
		return $notification;

	}


	/**
	 * Find booking with Token
	 *
	 * @param string $token
	 *
	 * @return array
	 */
	public function find_booking_with_token(string $token){
		global $wpdb;

		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE token = %s", $token ), ARRAY_A );

		return $booking;
	}

	/**
	 * Find GF entry with Token
	 *
	 * @param string $token
	 *
	 * @return array
	 */
	public function find_entry_with_token(string $token){
		global $wpdb;

		$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}gf_entry_meta WHERE meta_key = %s AND meta_value = %s", '_booking_token', $token ), ARRAY_A );
		$entry_id = $entry['entry_id'];
		if(!empty($entry_id)){
			$entry = GFAPI::get_entry( $entry_id );
		}

		return $entry;
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
	 * Find the field value with an inputname in a field list from a Gravity Form
	 *
	 * @param $find_inputname
	 * @param $fields
	 * @param $entry
	 *
	 * @return string
	 */
	static function field_value_from_inputname($find_inputname, $fields, $entry){

		return rgar($entry, self::field_id_from_inputname($find_inputname, $fields));

	}

	/**
	 * Find the field id with a class in a field list from a Gravity Form
	 *
	 * @param string $find_class
	 * @param array $fields
	 *
	 * @return string
	 */
	static function field_id_from_class( $find_class, $fields ) {

		foreach ( $fields as $field ) {

			$class = $field['cssClass'];
			if ( strpos( $class, $find_class ) !== false ) {
				return $field['id'];

			} else {
				if ( ! empty( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as $field_input ) {
						$class = $field_input['name'];
						if ( strpos( $class, $find_class ) !== false ) {
							return $field_input['id'];
						}
					}
				}
			}
		}
	}

	/**
	 * Find the field id with an inputname in a field list from a Gravity Form
	 *
	 * @param string $find_inputname
	 * @param array $fields
	 *
	 * @return string
	 */
	static function field_id_from_inputname($find_inputname, $fields){

		foreach($fields as $field){

			$class = $field['inputName'];
			if($class === $find_inputname){
				return $field['id'];

			}
			else{
				if(!empty($field['inputs'])){
					foreach($field['inputs'] as $field_input){
						$class = $field_input['name'];
						if($class === $find_inputname){
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

		//error_log('ajax_times');

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
	 * Returns Capacity Timeslots
	 *
	 * @return mixed|null
	 */
	private function capacity_periods_settings(){


		$attendance_options = self::attendance_options();
		if( empty($attendance_options) ){
			return null;
		}
		if( empty($attendance_options['timeslots'] ) ){
			return null;
		}

		return $attendance_options['timeslots'];

	}

	/**
	 * Returns (Capacity) Opening Times for the requested date
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
	 * Returns Allotment Timeslots
	 *
	 * @return mixed|null
	 */
	private function allotment_periods_settings(){

		return $this->get_option('timeslots');

	}

	/**
	 * Returns Allotment Opening Times for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array|null
	 */
	public function allotment_periods_forthedate( string $date = ''){

		$attendance_timeslots = self::allotment_periods_settings();

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
	public function allotment_timeslots_forthedate( string $date = ''){

		$total_capacity = [];

		$opening_periods = self::allotment_periods_forthedate( $date );
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
	 * Returns Treatments Capacity Timeslots
	 *
	 * @return mixed|null
	 */
	private function treatments_capacity_periods_settings(){

		return $this->get_option('treatmentcourse_allotment');

	}

	/**
	 * Returns (Treatments Capacity) Opening Times for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array|null
	 */
	public function treatments_capacity_periods_forthedate( string $date = ''){

		$attendance_timeslots = self::treatments_capacity_periods_settings();

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
	 * Returns Treatments Capacity for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array
	 */
	public function treatments_capacity_timeslots_forthedate( string $date = ''){

		$total_capacity = [];

		$opening_periods = self::treatments_capacity_periods_forthedate( $date );
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
	 * @return array
	 * @throws Exception
	 */
	private function _get_times() {
		global $wpdb;

		$user = wp_get_current_user();

		$times = [];
		$closed_dates = [];
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
			//error_log('$date:'.$date);
			//error_log('$participants:'.$participants);

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


			$capacity_forthedate_timeslots = self::allotment_timeslots_forthedate($date_with_dash);

			// If no timeslot, assume the course is closed
			if( array_sum($capacity_forthedate_timeslots) === 0){
				array_push($closed_dates, $date_with_dash);
			}

			//usort($slots_in_opening_hours, function($a, $b) {
			//	return strcmp($a['start'], $b['start']);
			//});
			ksort($capacity_forthedate_timeslots);
			//error_log('$capacity_forthedate_timeslots');
			//error_log(print_r($capacity_forthedate_timeslots , true));


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
					//error_log( 'slot: '.$slot_begin->format( "Y-m-d H:i:s" ) );

					$uses[$slot_begin->format( "Y-m-d H:i:s" )] = self::get_participants_ongoing_forthetime($slot_begin);

				}

			}



			//error_log('$uses');
			//error_log(print_r($uses, true));

			$all_slots = $capacity_forthedate_timeslots;
			$slots_available = [];
			// Get all available slots and calculate the remaining availability with participants number during all the duration of the slot
			$index = 1;
			foreach ($all_slots as $slot_begin => $capacity ){
				$slot_begin_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $slot_begin, wp_timezone());
				$slot_end_object = clone $slot_begin_object;
				$slot_end_object->modify( '+' . $averagecourse . ' minutes' );

				$allow_begin          = new DateTime( 'now', wp_timezone() );
				$allow_begin_customer = new DateTime( 'now', wp_timezone() );
				$allow_begin_customer->modify( '+' . ( 60 * $this->get_option( 'hoursbefore' ) ) . ' minutes' );
				$allow_end_customer = new DateTime( 'now', wp_timezone() );
				$allow_end_customer->modify( '+' . ( 60 * $this->get_option( 'hoursafter' ) ) . ' minutes' );

				// Is a customer
				if( $this->user_has_role(wp_get_current_user(), 'customer') || !$user || is_wp_error( $user ) || !$user->ID ) {
					if ( $slot_begin_object < $allow_begin_customer || $slot_begin_object > $allow_end_customer ) {
						continue;
					}
				}
				// Is not a customer allow timeslot before "hours before"
				else{
					if($slot_begin_object < $allow_begin ) {
						continue;
					}
				}


				$period = new DatePeriod( $slot_begin_object, $interval, $slot_end_object );

				//error_log('calculate capacity for '.$slot_begin_object->format( "Y-m-d H:i:s" ));

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
						'index' => $index,
					];
				}

				if(isset( $slots_available[$slot_begin] ) && $slots_available[$slot_begin]['capacity'] <= 0){
					unset($slots_available[$slot_begin]);
				}
				else{
					$index ++;
				}
			}

			//error_log('$slots_available');
			//error_log(print_r($slots_available , true));


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
			$errors[] = __( 'No time slot found', 'tmsm-aquatonic-course-booking' );
			$times[] = [
				'date' => $date_with_dash,
				'hour' => null,
				'minutes' => null,
				'hourminutes' => null,
				'priority' => null,
				'message' => in_array($date_with_dash, $closed_dates) ? __( 'Closed', 'tmsm-aquatonic-course-booking' ) : __( 'No time slot found', 'tmsm-aquatonic-course-booking' ),
			];
		}

		return $times;
	}


	/**
	 * Get participants number with an ongoing course for the time
	 *
	 * @param DateTimeInterface $datetime
	 *
	 * @return int
	 */
	public function get_participants_ongoing_forthetime( DateTimeInterface $datetime){
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

	/**
	 * Gravity Forms: Use WooCommerce emails templates for notifications.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function gform_html_message_template_pre_send_email( string $template ) {

		if ( function_exists( 'wc_get_template_html' ) && class_exists( 'WC_Email' ) ) {

			$header = wc_get_template_html(
				'emails/email-header.php',
				array(
					'email_heading' => '{subject}',
				)
			);

			$footer = wc_get_template_html( 'emails/email-footer.php' );

			$wc_email = new WC_Email();
			$template = $wc_email->style_inline( $header . '{message}' . $footer );

		}

		return $template;
	}

	/**
	 * Gravity Forms: Customize email notification (headers, message, to, subject) to use WooCommerce CSS styling.
	 *
	 * @param array  $email          An array containing the email to address, subject, message, headers, attachments and abort email flag.
	 * @param string $message_format The message format: html or text.
	 * @param array  $notification   The current Notification object.
	 * @param array  $entry          The current Entry object.
	 *
	 * @return array
	 */
	public function gform_pre_send_email( array $email, string $message_format, array $notification, array $entry ) {

		if ( class_exists( 'WC_Email' ) ) {

			$wc_email         = new WC_Email();
			$email['message'] = $wc_email->style_inline( $email['message'] );

		}

		return $email;
	}




	/**
	 * WooCommerce: Add custom CSS to emails
	 *
	 * @param string   $css
	 * @param WC_Email $email
	 *
	 * @return string
	 */
	public function woocommerce_email_styles(string $css, WC_Email $email){

		// Gmail Desktop doesnt support @media print

		/*$css .= '
		@media print{
			#template_header_image img{
				height: 120px !important;
			}
			#wrapper{
				padding: 0 !important;
			}
			#body_content table td {
				padding: 10px !important;
			}
			#wrapper table table {
                width: 100% !important;
			}
			#footer{
				display: none !important;
			}
			#credit{
				display: none !important;
			}
		}
		';*/

		$css .= '
		#wrapper{
			padding: 10px 0 0 !important;
		}
		#template_header_image img{
				height: 120px !important;
		}
		#header_wrapper{
			padding: 10px 15px;
		}
		h1{
			font-size: 20px;
		}
		#body_content table td {
			padding: 15px 15px 10px;
		}
		';

		return $css;
	}

	/**
	 * Filters the list of CSS body class names
	 *
	 * @since 2.8.0
	 *
	 * @param string[] $classes An array of body class names.
	 * @param string[] $class   An array of additional class names added to the body.
	 *
	 * @return string[]
	 */
	public function body_class_pages( $classes, $class ) {
		global $post;

		$cancel_page_id = $this->get_option( 'page_cancel_id' );
		$add_page_id    = $this->get_option( 'page_add_id' );

		if ( ! empty( $post ) && ! empty( $post->ID ) && in_array( $post->ID, [ $cancel_page_id, $add_page_id ] ) ) {
			$classes[] = 'tmsm-aquatonic-course-booking-pages';
		}

		return $classes;
	}


	/**
	 * Mini Dashboard (loaded with in ajax)
	 */
	public function minidashboard(){

		$minidashboard_values = get_option('tmsm-aquatonic-course-booking-minidashboard');

		//		<script type="text/javascript" src="/wp-admin/load-scripts.php?c=0&load%5Bchunk_0%5D=jquery-core,jquery-migrate"></script>
		//		<script src="'.TMSM_AQUATONIC_COURSE_BOOKING_URL.'admin/js/jquery.countdown.min.js"></script>
		//      <script src="'.TMSM_AQUATONIC_COURSE_BOOKING_URL.'admin/js/tmsm-aquatonic-course-booking-admin.js"></script>
		echo '<html lang="en">
		<head>
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<title>'.__('Mini Dashboard', 'tmsm-aquatonic-course-booking').'</title>
		<style>th{font-weight: bold;}
		body{min-width: inherit !important; overflow: hidden !important;}
		.wrap{margin:0 5px !important;}
		h3{margin:5px 0!important;}
		.nowrap{white-space: nowrap}
		table{margin-bottom:10px !important;}
		</style>
		<meta http-equiv="refresh" content="'. (MINUTE_IN_SECONDS * 5) .' ">
		
		
		
		<link rel="stylesheet" href="/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load%5Bchunk_0%5D=dashicons,admin-bar,common,forms,admin-menu,dashboard,list-tables,edit,revisions,media,themes,about,nav-menus,wp-pointer,widgets&amp;load%5Bchunk_1%5D=,site-icon,l10n,buttons,wp-auth-check,media-views" type="text/css" media="all">
		</head><body><div class="wrap">';


		ksort($minidashboard_values);
		echo '<h3>'.__('Aquatonic Course', 'tmsm-aquatonic-course-booking').'</h3>';
		echo '</div>';
		//print_r($minidashboard_values);
		if( ! empty($minidashboard_values)){
			echo '<table class="wp-list-table widefat striped table-dashboard"><thead>';
			foreach($minidashboard_values as $counter => $value){
				echo '<tr><'.($counter==0 ? 'th' : 'td') .'>'.(esc_html($value['date']) ?? '' ).'</td><td>'.( esc_html($value['free']) ?? '' ).'</'.($counter==1 ? 'th' : 'td') .'></tr>';
				if ($counter==0){
					echo '</thead><tbody>';
				}

			}
			echo '</tbody></table>';
		}
		echo '<div class="wrap">';


		//$setting_url = admin_url( 'admin.php'. '?page='.$this->plugin_name.'-settings' ) ;
		//echo '<p><a target="_blank" href="'.$setting_url.'">'.__('Access the main dashboard', 'tmsm-aquatonic-course-booking').'</a></p>';

		/*if( has_action('tmsm_aquatonic_attendance_cronaction')){
			$cronevent = wp_next_scheduled( 'tmsm_aquatonic_attendance_cronaction' );
			$cronevent += 1 * MINUTE_IN_SECONDS; // Add 30 seconds to let cron event execute

			// Crontevent next schedule is in the past, add 5 minutes
			if($cronevent < time()){
				$cronevent = $cronevent + 5 * MINUTE_IN_SECONDS;
			}
			if( ! empty($cronevent)){
				$date = wp_date( get_option( 'time_format' ), $cronevent );
				echo sprintf(__( 'Next Refresh at %s in %s', 'tmsm-aquatonic-course-booking' ), '<span class="nowrap">'.$date.'</span>', '<b class="refresh nowrap" id="refresh-counter" data-time="'.esc_attr($cronevent).'"></b>');

				?>
				<?php
			}
		}*/
		echo '</div></div>

		
		</body></html>';
		die();
	}


}
