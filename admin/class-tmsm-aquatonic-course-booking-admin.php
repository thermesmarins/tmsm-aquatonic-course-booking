<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/admin
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Course_Booking_Admin {

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
	 * The plugin options.
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$options    The plugin options.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->set_options();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-course-booking-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'jquery.countdown', plugin_dir_url( __FILE__ ) . 'js/jquery.countdown.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-course-booking-admin.js', array( 'jquery', 'jquery.countdown' ), $this->version, true );

		// Javascript localization
		$translation_array = array(
			'data' => [
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'tmsm-aquatonic-course-booking-nonce-action' ),
			],
			'i18n' => [
				'surecancel' => __( 'Are you sure you want to cancel this booking?', 'tmsm-aquatonic-course-booking' ),
			] ,

		);
		wp_localize_script( $this->plugin_name, 'TmsmAquatonicCourseAdmin', $translation_array );
	}


	/**
	 * Register the Settings page.
	 *
	 * @since    1.0.0
	 */
	public function options_page_menu() {

		$current_user_id = get_current_user_id();

		$target_roles = array('administrator');
		$user_meta = get_userdata($current_user_id);
		$user_roles = ( array ) $user_meta->roles;

		if ( array_intersect($target_roles, $user_roles) ) {
			add_options_page( __('Aquatonic Course', 'tmsm-aquatonic-course-booking'), __('Aquatonic Course', 'tmsm-aquatonic-course-booking'), 'aquatonic_course', $this->plugin_name.'-settings', array($this, 'options_page_display'));

		}
		else{
			add_menu_page(__('Aquatonic Course', 'tmsm-aquatonic-course-booking'), __('Aquatonic Course', 'tmsm-aquatonic-course-booking'), 'aquatonic_course', $this->plugin_name.'-settings', array($this, 'options_page_display'));

		}

	}


	/**
	 * Admin Page URL
	 *
	 * @return string
	 */
	private function admin_page_url(){

		$screen = get_current_screen();
		if(strpos($screen->base, 'toplevel' ) === false){
			return 'options-general.php';
		}
		else{
			return 'admin.php';
		}
	}

	/**
	 * Plugin Settings Link on plugin page
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	function settings_link( $links ) {
		$setting_link = array(
			'<a href="' . admin_url( self::admin_page_url(). '?page='.$this->plugin_name.'-settings' ) . '">'.__('Settings', 'tmsm-aquatonic-course-booking').'</a>',
		);
		return array_merge( $setting_link, $links );
	}


	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function options_page_display() {
		$tab = ( isset($_GET['tab']) ) ? sanitize_text_field($_GET['tab']) : 'dashboard';

		global $wpdb;

		$bookings_of_the_day = $this->bookings_of_the_day();

		include_once( 'partials/' . $this->plugin_name . '-admin-options-page.php' );
	}

	/**
	 * @return array|object|null
	 */
	public function bookings_of_the_day(){
		global $wpdb;
		$today = new  DateTime();
		$tomorrow = clone $today;
		$tomorrow->modify('+1 day');
		$bookings_of_the_day = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM
{$wpdb->prefix}aquatonic_course_booking WHERE course_start >= %s AND course_end < %s ORDER BY course_start", $today->format( "Y-m-d" ).' 00:00:00', $tomorrow->format( "Y-m-d" ).' 00:00:00' ) );
		return $bookings_of_the_day;
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_times( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-times.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_form( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-form.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_dialoginsight( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-dialoginsight.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_aquos( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-aquos.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_tests( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-tests.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_googlepaypasses( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-googlepaypasses.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_customeralliance( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-customeralliance.php' );
	}

	/**
	 * Registers settings fields with WordPress
	 */
	public function register_fields() {

		add_settings_field(
			'slotsize',
			esc_html__( 'Slot Size', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_select' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Number of slots per hour', 'tmsm-aquatonic-course-booking' ),
				'id' => 'slotsize',
				'selections' => [
					2 => 2,
					3 => 3,
					4 => 4,
					6 => 6,
				],
			)
		);

		add_settings_field(
			'courseaverage',
			esc_html__( 'Course Average', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Number of minutes of an average course', 'tmsm-aquatonic-course-booking' ),
				'id' => 'courseaverage',
			)
		);

		add_settings_field(
			'lessonbefore',
			esc_html__( 'Lesson Considered Before', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Number of minutes before a lesson, the person is considered in the course', 'tmsm-aquatonic-course-booking' ),
				'id' => 'lessonbefore',
			)
		);

		add_settings_field(
			'lessonafter',
			esc_html__( 'Lesson Considered After', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Number of minutes after a lesson, the person is considered in the course', 'tmsm-aquatonic-course-booking' ),
				'id' => 'lessonafter',
			)
		);

		add_settings_field(
			'blockedbeforedate',
			esc_html__( 'Blocked Before Date', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Date when reservations can start. Format: YYYY-MM-DD', 'tmsm-aquatonic-course-booking' ),
				'id' => 'blockedbeforedate',
			)
		);

		add_settings_field(
			'hoursbefore',
			esc_html__( 'Booking Possible Before', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Number of hours before the possibility to book', 'tmsm-aquatonic-course-booking' ),
				'id' => 'hoursbefore',
			)
		);

		add_settings_field(
			'hoursafter',
			esc_html__( 'Booking Possible After', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'description' 	=> __( 'Number of hours after the possibility to book', 'tmsm-aquatonic-course-booking' ),
				'id' => 'hoursafter',
			)
		);


		add_settings_field(
			'timeslots',
			esc_html__( 'Booking Allotments for Course', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_textarea' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'id' => 'timeslots',
				'rows' => 20,
				'description' => esc_html__( 'Format: Day Number=09:00-14:00,15:30-17:30 serapated by a line break. Day Number is: 0 for Sunday, 1 for Monday, etc. Also for special dates: Date=09:00-21:00=0 where Date is in format YYYY-MM-DD.', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'treatmentcourse_allotment',
			esc_html__( 'Allotments for Treatment+Course', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_textarea' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'id' => 'treatmentcourse_allotment',
				'rows' => 10,
				'description' => esc_html__( 'Format: Day Number=09:00-14:00,15:30-17:30 serapated by a line break. Day Number is: 0 for Sunday, 1 for Monday, etc. Also for special dates: Date=09:00-21:00=0 where Date is in format YYYY-MM-DD.', 'tmsm-aquatonic-course-booking' ),
			)
		);

		$forms = [];
		$pages = [];
		$forms[] = ['value' => '', 'label' => __( 'None', 'tmsm-aquatonic-course-booking' )];
		$pages[] = ['value' => '', 'label' => __( 'None', 'tmsm-aquatonic-course-booking' )];

		// Select frontend form
		if(class_exists('GFAPI')){
			foreach (GFAPI::get_forms() as $form){
				$forms[] = ['value' => $form['id'], 'label' => $form['title']];
			}

			// Select pages
			foreach (get_pages(['sort_order' => 'DESC', 'sort_column' => 'date']) as $page){
				$pages[] = ['value' => $page->ID, 'label' => $page->post_title];
			}

		}

		add_settings_field(
			'gform_add_id',
			esc_html__( 'Gravity Form for adding a booking', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_select' ),
			$this->plugin_name,
			$this->plugin_name . '-form',
			array(
				'id' => 'gform_add_id',
				'selections' => $forms,
			)
		);

		add_settings_field(
			'page_add_id',
			esc_html__( 'Page for adding a booking', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_select' ),
			$this->plugin_name,
			$this->plugin_name . '-form',
			array(
				'id' => 'page_add_id',
				'selections' => $pages,
			)
		);

		add_settings_field(
			'gform_cancel_id',
			esc_html__( 'Gravity Form for cancelling a booking', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_select' ),
			$this->plugin_name,
			$this->plugin_name . '-form',
			array(
				'id' => 'gform_cancel_id',
				'selections' => $forms,
			)
		);

		add_settings_field(
			'page_cancel_id',
			esc_html__( 'Page for cancelling a booking', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_select' ),
			$this->plugin_name,
			$this->plugin_name . '-form',
			array(
				'id' => 'page_cancel_id',
				'selections' => $pages,
			)
		);

		add_settings_field(
			'dialoginsight_idkey',
			esc_html__( 'Dialog Insight Key ID', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-dialoginsight',
			array(
				'id' => 'dialoginsight_idkey',
			)
		);

		add_settings_field(
			'dialoginsight_apikey',
			esc_html__( 'Dialog Insight API Key', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-dialoginsight',
			array(
				'id' => 'dialoginsight_apikey',
			)
		);

		add_settings_field(
			'dialoginsight_idproject',
			esc_html__( 'Dialog Insight Project ID', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-dialoginsight',
			array(
				'id' => 'dialoginsight_idproject',
			)
		);

		add_settings_field(
			'dialoginsight_relationaltableid',
			esc_html__( 'Dialog Insight Relational Table ID', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-dialoginsight',
			array(
				'id' => 'dialoginsight_relationaltableid',
				'description' => esc_html__( 'Relational Table for Course Bookings', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'dialoginsight_sourcecode',
			esc_html__( 'Dialog Insight Source Code', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-dialoginsight',
			array(
				'id' => 'dialoginsight_sourcecode',
				'description' => esc_html__( 'Source Code in Dialog Insight', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'dialoginsight_beneficiaryfield',
			esc_html__( 'Dialog Insight Beneficiary Field', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-dialoginsight',
			array(
				'id' => 'dialoginsight_beneficiaryfield',
				'description' => esc_html__( 'Beneficiary Field in Dialog Insight', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'aquos_endpoint_contact',
			esc_html__( 'Aquos Endpoint for Adding Contact', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-aquos',
			array(
				'id' => 'aquos_endpoint_contact',
				'description' => esc_html__( 'URL to endpoint', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'aquos_endpoint_lessons',
			esc_html__( 'Aquos Endpoint for Lessons', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-aquos',
			array(
				'id' => 'aquos_endpoint_lessons',
				'description' => esc_html__( 'URL to endpoint', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'aquos_siteid',
			esc_html__( 'Aquos Site ID', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-aquos',
			array(
				'id' => 'aquos_siteid',
			)
		);

		$aquos_secret = $this->get_option('aquos_secret') ?? wp_generate_password( 50, true, true );

		add_settings_field(
			'aquos_secret',
			esc_html__( 'Aquos Secret', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-aquos',
			array(
				'id' => 'aquos_secret',
				'readonly' => true,
				'value' => $aquos_secret,
			)
		);

		add_settings_field(
			'tests_lessonsdate',
			esc_html__( 'Lessons Date', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-tests',
			array(
				'id' => 'tests_lessonsdate',
				'description' => esc_html__( 'Date Format: Ymd. Example: 20210428', 'tmsm-aquatonic-course-booking' ),
			)
		);

		add_settings_field(
			'tests_realtimeattendance',
			esc_html__( 'Real Time Attendance', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-tests',
			array(
				'id' => 'tests_realtimeattendance',
			)
		);

		$options[] = array( 'googlepaypasses_accountemail', 'text', '' );
		$options[] = array( 'googlepaypasses_accountfilepath', 'text', '' );
		$options[] = array( 'googlepaypasses_applicationname', 'text', '' );
		$options[] = array( 'googlepaypasses_issuerid', 'text', '' );

		add_settings_field(
			'googlepaypasses_accountemail',
			esc_html__( 'Account Email', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-googlepaypasses',
			array(
				'id' => 'googlepaypasses_accountemail',
			)
		);

		add_settings_field(
			'googlepaypasses_accountfilepath',
			esc_html__( 'Account File Path', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-googlepaypasses',
			array(
				'id' => 'googlepaypasses_accountfilepath',
			)
		);

		add_settings_field(
			'googlepaypasses_applicationname',
			esc_html__( 'Application Name', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-googlepaypasses',
			array(
				'id' => 'googlepaypasses_applicationname',
			)
		);

		add_settings_field(
			'googlepaypasses_issuerid',
			esc_html__( 'Issuer ID', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-googlepaypasses',
			array(
				'id' => 'googlepaypasses_issuerid',
			)
		);

		add_settings_field(
			'customeralliance_accesskey',
			esc_html__( 'Access Key', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-customeralliance',
			array(
				'id' => 'customeralliance_accesskey',
			)
		);

		add_settings_field(
			'customeralliance_reviewsubject',
			esc_html__( 'Review Subject', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-customeralliance',
			array(
				'id' => 'customeralliance_reviewsubject',
			)
		);

	}

	/**
	 * Registers settings sections with WordPress
	 */
	public function register_sections() {


		add_settings_section(
			$this->plugin_name . '-times',
			esc_html__( 'Times', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_times' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-form',
			esc_html__( 'Form', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_form' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-dialoginsight',
			esc_html__( 'Dialog Insight', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_dialoginsight' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-aquos',
			esc_html__( 'Aquos', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_aquos' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-tests',
			esc_html__( 'Tests', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_tests' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-googlepaypasses',
			esc_html__( 'Google Pay Passes', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_googlepaypasses' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-customeralliance',
			esc_html__( 'Customer Alliance', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'section_customeralliance' ),
			$this->plugin_name
		);

	}


	/**
	 * Registers plugin settings
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function register_settings() {
		register_setting(
			$this->plugin_name . '-options',
			$this->plugin_name . '-options',
			array( $this, 'validate_options' )
		);
	}


	/**
	 * Check the Gravity Forms Add form
	 *
	 * @param int $gform_id
	 */
	static function gform_check_add_form( $gform_id){

		$wp_error = new WP_Error();
		if(!empty($gform_id)){
			$form = GFAPI::get_form( $gform_id );
			if(empty($form)){
				$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The form was not found', 'tmsm-aquatonic-course-booking'));
			}
			else{

				$feeds = GFAPI::get_feeds(null, $gform_id, 'tmsm-gravityforms-dialoginsight', true	);
				if(empty($feeds)){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The Dialog Insight feed is missing', 'tmsm-aquatonic-course-booking'));
				}

				if($form['cssClass'] !== 'tmsm-aquatonic-course-form-add' ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The form layout needs the `tmsm-aquatonic-course-form-add` class', 'tmsm-aquatonic-course-booking'));
				}

				$name = false;
				$email = false;
				$phone = false;
				$birthdate = false;
				$address = false;
				$participants = false;
				$times = false;
				$summary = false;
				$title = false;
				foreach($form['fields'] as $field){

					if($field['cssClass'] === 'tmsm-aquatonic-course-name'){
						$name = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-email'){
						$email = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-birthdate'){
						$birthdate = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-address'){
						$address = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-phone'){
						$phone = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-participants'){
						$participants = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-times'){
						$times = true;
					}
					if( strpos($field['cssClass'], 'tmsm-aquatonic-course-summary') !== false ){
						$summary = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-title'){
						$title = true;
					}

				}

				if( ! $name ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The name field needs the `tmsm-aquatonic-course-name` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $email ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The name field needs the `tmsm-aquatonic-course-email` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $birthdate ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The birthdate field needs the `tmsm-aquatonic-course-birthdate` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $address ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The address field needs the `tmsm-aquatonic-course-address` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $phone ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The phone field needs the `tmsm-aquatonic-course-phone` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $participants ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The participants field needs the `tmsm-aquatonic-course-participants` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $times ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The times field needs the `tmsm-aquatonic-course-times` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $summary ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The times field needs the `tmsm-aquatonic-course-summary` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $title ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The title field needs the `tmsm-aquatonic-course-title` class', 'tmsm-aquatonic-course-booking'));
				}
			}

		}


		if ( isset( $wp_error ) && is_wp_error( $wp_error ) && $wp_error->has_errors()){
			add_settings_error( 'gform_add', 'gform_add_errors',
				sprintf(__( 'Gravity Forms Add Form setup has failed: %s', 'tmsm-aquatonic-course-booking' ), join(',', $wp_error->get_error_messages())), 'error' );
			settings_errors('gform_add');
		}

	}
	/**
	 * Check the Gravity Forms Cancel form
	 *
	 * @param int $gform_id
	 */
	static function gform_check_cancel_form( $gform_id){

		$wp_error = new WP_Error();
		if(!empty($gform_id)){
			$form = GFAPI::get_form( $gform_id );

			if(empty($form)){
				$wp_error->add('tmsm-aquatonic-course-booking-gform-cancel', __('The form was not found', 'tmsm-aquatonic-course-booking'));
			}
			else{

				if($form['cssClass'] !== 'tmsm-aquatonic-course-form-cancel' ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-cancel', __('The form layout needs the `tmsm-aquatonic-course-form-cancel` class', 'tmsm-aquatonic-course-booking'));
				}

				$token = false;
				$summary = false;
				foreach($form['fields'] as $field){

					if($field['inputName'] === 'booking_token'){
						$token = true;
					}
					if( strpos($field['cssClass'], 'tmsm-aquatonic-course-summary') !== false ){
						$summary = true;
					}

				}

				if( ! $token ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The name field needs the `tmsm-aquatonic-course-name` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $summary ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The name field needs the `tmsm-aquatonic-course-name` class', 'tmsm-aquatonic-course-booking'));
				}

			}


		}

		if ( isset( $wp_error ) && is_wp_error( $wp_error ) && $wp_error->has_errors()){
			add_settings_error( 'gform_cancel', 'gform_cancel_errors',
				sprintf(__( 'Gravity Forms Cancel Form setup has failed: %s', 'tmsm-aquatonic-course-booking' ), join(',', $wp_error->get_error_messages())), 'error' );
			settings_errors('gform_cancel');
		}

	}

	/**
	 * Sanitize fields
	 *
	 * @param $type
	 * @param $data
	 *
	 * @return string|void
	 */
	private function sanitizer( $type, $data ) {
		if ( ! isset( $type ) ) { return; }
		if ( ! isset( $data ) ) { return; }
		$return 	= '';
		$sanitizer 	= new Tmsm_Aquatonic_Course_Booking_Sanitize();
		$sanitizer->set_data( $data );
		$sanitizer->set_type( $type );
		$return = $sanitizer->clean();
		unset( $sanitizer );
		return $return;
	}

	/**
	 * Sets the class variable $options
	 */
	private function set_options() {
		$this->options = get_option( $this->plugin_name . '-options' );
	}

	/**
	 * Gets the class variable $options
	 */
	public function get_options() {
		return get_option( $this->plugin_name . '-options' );
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
	 * Validates saved options
	 *
	 * @since 		1.0.0
	 * @param 		array 		$input 			array of submitted plugin options
	 * @return 		array
	 * @throws Exception
	 */
	public function validate_options( $input ) {

		$valid 		= array();
		$options 	= $this->get_options_list();
		foreach ( $options as $option ) {
			$name = $option[0];
			$type = $option[1];

			$valid[$option[0]] = $this->sanitizer( $type, $input[$name] );

		}


		if ( empty( $input['gform_add_id'] ) || empty( $input['gform_cancel_id'] ) ) {
			add_settings_error( 'gform_add_id', 'gform_errors', __( 'Gravity Forms all need to be defined', 'tmsm-aquatonic-course-booking' ),
				'error' );
		}

		if ( empty( $input['page_add_id'] ) || empty( $input['page_cancel_id'] ) ) {
			add_settings_error( 'page_add_id', 'gform_errors', __( 'Pages all need to be defined', 'tmsm-aquatonic-course-booking' ),
				'error' );
		}

		if ( empty( $input['courseaverage'] ) || empty( $input['hoursafter'] ) || empty( $input['timeslots'] ) ) {
			add_settings_error( 'courseaverage', 'timeslots_errors', __( 'Timeslots fields all need to be defined', 'tmsm-aquatonic-course-booking' ),
				'error' );
		}

		if ( empty( $input['dialoginsight_idkey'] ) || empty( $input['dialoginsight_apikey'] ) || empty( $input['dialoginsight_idproject'] )
		     || empty( $input['dialoginsight_relationaltableid'] ) || empty( $input['dialoginsight_sourcecode'] ) ) {
			add_settings_error( 'dialoginsight_idkey', 'dialoginsight_errors',
				__( 'Dialog Insight fields all need to be defined', 'tmsm-aquatonic-course-booking' ), 'error' );
		}

		return $valid;
	}

	/**
	 * Creates a checkbox field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_checkbox( $args ) {
		$defaults['class'] 			= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['value'] 			= 0;
		apply_filters( $this->plugin_name . '-field-checkbox-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-checkbox.php' );
	}

	/**
	 * Creates an editor field
	 *
	 * NOTE: ID must only be lowercase letter, no spaces, dashes, or underscores.
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_editor( $args ) {
		$defaults['description'] 	= '';
		$defaults['settings'] 		= array( 'textarea_name' => $this->plugin_name . '-options[' . $args['id'] . ']' );
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-editor-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-editor.php' );
	}

	/**
	 * Creates a set of radios field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_radios( $args ) {
		$defaults['class'] 			= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['value'] 			= 0;
		apply_filters( $this->plugin_name . '-field-radios-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-radios.php' );
	}

	public function field_repeater( $args ) {
		$defaults['class'] 			= 'repeater';
		$defaults['fields'] 		= array();
		$defaults['id'] 			= '';
		$defaults['label-add'] 		= 'Add Item';
		$defaults['label-edit'] 	= 'Edit Item';
		$defaults['label-header'] 	= 'Item Name';
		$defaults['label-remove'] 	= 'Remove Item';
		$defaults['title-field'] 	= '';
		/*
				$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		*/
		apply_filters( $this->plugin_name . '-field-repeater-options-defaults', $defaults );
		$setatts 	= wp_parse_args( $args, $defaults );
		$count 		= 1;
		$repeater 	= array();
		if ( ! empty( $this->options[$setatts['id']] ) ) {
			$repeater = maybe_unserialize( $this->options[$setatts['id']][0] );
		}
		if ( ! empty( $repeater ) ) {
			$count = count( $repeater );
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-repeater.php' );
	}

	/**
	 * Creates a select field
	 *
	 * Note: label is blank since its created in the Settings API
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_select( $args ) {
		$defaults['aria'] 			= '';
		$defaults['blank'] 			= '';
		$defaults['class'] 			= 'widefat';
		$defaults['context'] 		= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['selections'] 	= array();
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-select-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		if ( empty( $atts['aria'] ) && ! empty( $atts['description'] ) ) {
			$atts['aria'] = $atts['description'];
		} elseif ( empty( $atts['aria'] ) && ! empty( $atts['label'] ) ) {
			$atts['aria'] = $atts['label'];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-select.php' );
	}

	/**
	 * Creates a text field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_text( $args ) {
		$defaults['class'] 			= 'regular-text';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['placeholder'] 	= '';
		$defaults['type'] 			= 'text';
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-text-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( isset( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-text.php' );
	}

	/**
	 * Creates a textarea field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_textarea( $args ) {
		$defaults['class'] 			= 'large-text';
		$defaults['cols'] 			= 50;
		$defaults['context'] 		= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['rows'] 			= 10;
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-textarea-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-textarea.php' );
	}

	/**
	 * Returns an array of options names, fields types, and default values
	 *
	 * @return 		array 			An array of options
	 */
	public static function get_options_list() {
		$options   = array();

		$options[] = array( 'slotsize', 'text', '4' );
		$options[] = array( 'courseaverage', 'text', '90' );
		$options[] = array( 'blockedbeforedate', 'text', '' );
		$options[] = array( 'hoursbefore', 'text', '' );
		$options[] = array( 'hoursafter', 'text', '' );
		$options[] = array( 'lessonbefore', 'text', '0' );
		$options[] = array( 'lessonafter', 'text', '0' );
		$options[] = array( 'timeslots', 'textarea', '' );
		$options[] = array( 'treatmentcourse_allotment', 'textarea', '' );

		$options[] = array( 'gform_add_id', 'text', '' );
		$options[] = array( 'gform_cancel_id', 'text', '' );
		$options[] = array( 'page_add_id', 'text', '' );
		$options[] = array( 'page_cancel_id', 'text', '' );

		$options[] = array( 'dialoginsight_idkey', 'text', '' );
		$options[] = array( 'dialoginsight_apikey', 'text', '' );
		$options[] = array( 'dialoginsight_idproject', 'text', '' );
		$options[] = array( 'dialoginsight_relationaltableid', 'text', '' );
		$options[] = array( 'dialoginsight_sourcecode', 'text', '' );
		$options[] = array( 'dialoginsight_beneficiaryfield', 'text', '' );

		$options[] = array( 'aquos_endpoint_lessons', 'text', '' );
		$options[] = array( 'aquos_endpoint_contact', 'text', '' );
		$options[] = array( 'aquos_siteid', 'text', '' );
		$options[] = array( 'aquos_secret', 'text', '' );

		$options[] = array( 'tests_lessonsdate', 'text', '' );
		$options[] = array( 'tests_realtimeattendance', 'text', '' );

		$options[] = array( 'googlepaypasses_accountemail', 'text', '' );
		$options[] = array( 'googlepaypasses_accountfilepath', 'text', '' );
		$options[] = array( 'googlepaypasses_applicationname', 'text', '' );
		$options[] = array( 'googlepaypasses_issuerid', 'text', '' );

		$options[] = array( 'customeralliance_accesskey', 'text', '' );
		$options[] = array( 'customeralliance_reviewsubject', 'text', '' );

		return $options;
	}

	/*
	 * Refresh every 5 minutes the dashboard page
	 */
	public function dashboard_refresh(){
		global $pagenow;
		$screen = get_current_screen();
		if( $pagenow === self::admin_page_url() && $screen && $screen->id === 'settings_page_tmsm-aquatonic-course-booking-settings' && empty($_REQUEST['tab']) ){
			echo '<meta http-equiv="refresh" content="' . (MINUTE_IN_SECONDS * 5) . '; url='.self::admin_page_url().'?page=tmsm-aquatonic-course-booking-settings">';
		}
	}

	/**
	 * Mark Bookings as No Show
	 */
	public function bookings_mark_as_noshow(){
		global $wpdb;

		$nowminus15minutes = new  DateTime('now', wp_timezone());
		$nowminus15minutes->modify('-15 minutes');

		$mark_as_noshow_query = $wpdb->query( $wpdb->prepare( "UPDATE
{$wpdb->prefix}aquatonic_course_booking SET status='noshow' WHERE status = %s AND course_start < %s", 'active', $nowminus15minutes->format( "Y-m-d H:i:s" ) ) );

	}

	/**
	 * Mark Booking as Arrived
	 *
	 * @param array $booking
	 * @param bool $redirect_to_admin
	 *
	 * @return WP_Error
	 */
	public function booking_mark_as_arrived( $booking, $redirect_to_admin ){

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log('booking_mark_as_arrived');
		}

		// Don't call Dialog Insight if booking has no email since booking was not created in the relational table
		if ( empty( $booking['email'] ) ) {
			return;
		}

		// Don't call Dialog Insight if booking is not for self
		if ( $booking['self'] == 0 ) {
			return;
		}

		// Dialog Insight: Mark contact as customer if arrived
		$booking_dialoginsight = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Booking();
		$booking_dialoginsight->token = $booking['token'];
		$booking_dialoginsight->status = 'arrived';

		try {
			$booking_dialoginsight->update();

			// Booking updated and contact_id found
			if( ! empty($booking_dialoginsight->contact_id)){
				$contact = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Contact();
				$contact->contact_id = $booking_dialoginsight->contact_id;
				$contact->beneficiary = 1;
				$contact->update_by_id();
			}

		} catch (Exception $exception) {

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('Dialog Insight not updated: '. $exception->getMessage());
			}

			if($redirect_to_admin === false){
				wp_send_json( array( 'success' => false, 'message' => $exception->getMessage() ) );
			}

		}

	}

	/**
	 * Mark Booking as Cancelled
	 *
	 * @param array $booking
	 * @param bool $redirect_to_admin
	 *
	 * @return WP_Error
	 */
	public function booking_mark_as_cancelled( $booking, $redirect_to_admin ){

		// Don't call Dialog Insight if booking has no email since booking was not created in the relational table
		if ( empty( $booking['email'] ) ) {
			return;
		}

		// Don't call Dialog Insight if booking is not for self
		if ( $booking['self'] == 0 ) {
			return;
		}

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log('booking_mark_as_cancelled');
		}

		// Dialog Insight: Mark contact as customer if arrived
		$booking_dialoginsight = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Booking();
		$booking_dialoginsight->token = $booking['token'];
		$booking_dialoginsight->status = 'cancelled';
		try {
			$booking_dialoginsight->update();
		} catch (Exception $exception) {

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('Dialog Insight not updated: '. $exception->getMessage());
			}

			if($redirect_to_admin === false){
				wp_send_json( array( 'success' => false, 'message' => $exception->getMessage() ) );
			}

		}

	}

	/**
	 * Check ajax calls nonce
	 *
	 * @return bool
	 */
	function verify_ajax($action) {

		$nonce = isset($_REQUEST['tmsm_aquatonic_course_booking_nonce']) ? $_REQUEST['tmsm_aquatonic_course_booking_nonce'] : '';

		// Bail early if not nonce
		if( !$nonce || !wp_verify_nonce($nonce, $action) ) {
			return false;
		}

		return true;
	}

	/**
	 * Action Booking Change Status (Ajax)
	 *
	 * @throws Exception
	 */
	public function booking_change_status(){
		global $wpdb;

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log('booking_change_status');
		}

		$barcode = sanitize_text_field($_REQUEST['barcode'] ?? null);
		$status = sanitize_text_field($_REQUEST['status'] ?? null);

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log('barcode requested: '.$barcode);
		}

		$redirect_to_admin = true;

		if(! empty($barcode)){
			$redirect_to_admin = false;
		}

		if( $redirect_to_admin === false){

			$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE barcode = %s ", $barcode ), ARRAY_A );

			// Booking doesnt exist
			if( empty($booking)) {
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log( esc_html__( 'Booking not found', 'tmsm-aquatonic-course-booking' ));
				}

				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Booking not found', 'tmsm-aquatonic-course-booking' ) ) );
			}

			$booking_id = $booking['booking_id'];

			// Booking is already arrived
			if($booking['status'] === 'arrived' && $status === 'arrived'){
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log( esc_html__( 'Participant is already arrived', 'tmsm-aquatonic-course-booking' ));
				}

				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Participant is already arrived', 'tmsm-aquatonic-course-booking' ) ) );

			}

			// Booking is cancelled
			if($booking['status'] === 'cancelled'){
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log( esc_html__( 'Booking was cancelled', 'tmsm-aquatonic-course-booking' ));
				}

				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Booking was cancelled', 'tmsm-aquatonic-course-booking' ) ) );

			}

			// Booking date passed or in the future
			$now = new Datetime();
			$booking_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());

			if($booking_start_object->format('Y-m-d') !== $now->format('Y-m-d')){
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log(esc_html__( 'Booking date is passed or in the future', 'tmsm-aquatonic-course-booking' ));
				}

				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Booking date is passed or in the future', 'tmsm-aquatonic-course-booking' ) ) );

			}


		}
		else{
			if( ! check_admin_referer( 'tmsm_aquatonic_course_booking_change_status', 'tmsm_aquatonic_course_booking_nonce' )) die();

			// validate
			if( ! $this->verify_ajax('tmsm_aquatonic_course_booking_change_status') ) die();

			$booking_id = sanitize_text_field($_REQUEST['booking_id']);
		}

		// Update booking with new status
		if( $this->booking_is_valid_status($status) && !empty($booking_id) ){
			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('booking_is_valid_status');
			}
			$booking_update = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}aquatonic_course_booking SET status = %s WHERE booking_id= %d ", $status, $booking_id ) );

			if($status === 'arrived'){
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE booking_id= %d ", $booking_id ), ARRAY_A );

				$this->booking_mark_as_arrived($booking, $redirect_to_admin);
				$message = esc_html__( 'Booking valid for %s %s at %s for %s participant(s)', 'tmsm-aquatonic-course-booking' );

			}

			if($status === 'cancelled'){
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log('status cancelled');
				}
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE booking_id= %d ", $booking_id ), ARRAY_A );

				$this->booking_mark_as_cancelled($booking, $redirect_to_admin);
				$message = esc_html__( 'Cancelled booking for %s %s at %s', 'tmsm-aquatonic-course-booking' );

			}

		} // valid status

		// Return JSON
		if($redirect_to_admin === false){

			$booking_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());
			$date = wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option('date_format'), get_option('time_format') ) , $booking_start_object->getTimestamp() );

			wp_send_json(array(
				'success' => true,
				'message' => sprintf($message , $booking['firstname'], $booking['lastname'], $date, $booking['participants']) )
			);
		}
		// Or redirect
		else{
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'options-general.php?page=tmsm-aquatonic-course-booking-settings' ) );
		}

		exit;
	}


	/**
	 * Returns booking statuses
	 *
	 * @return array
	 */
	static function booking_statuses(){
		$statuses = [
			'active' => [
				'name' => __( 'Active', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Mark as active', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'booking-status booking-status-active',
			],
			'arrived' => [
				'name' => __( 'Arrived', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Arrived', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'booking-status booking-status-arrived',
				'actionclass' => 'button',
			],
			'cancelled' => [
				'name' => __( 'Cancelled', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Mark as cancelled', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'booking-status booking-status-cancelled',
				'actionclass' => 'button cancelled',
			],
			'noshow' => [
				'name' => __( 'No-show', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Mark as no-show', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'booking-status booking-status-noshow',
			],
		];
		return $statuses;
	}

	/**
	 * Returns if status is valid
	 *
	 * @param string $status
	 *
	 * @return bool
	 */
	public function booking_is_valid_status(string $status){

		return array_key_exists($status, $this->booking_statuses());

	}

	/**
	 * Get slotsize in minutes
	 *
	 * @return int
	 */
	public function slotsize_minutes(){

		$slotsize    = $this->get_option('slotsize');
		$slotminutes = 15;
		if ( $slotsize == 6 ) {
			$slotminutes = 10;
		}
		if ( $slotsize == 3 ) {
			$slotminutes = 20;
		}
		if ( $slotsize == 2 ) {
			$slotminutes = 30;
		}
		return $slotminutes;
	}

	public function get_attendance_realtime_count(){

		$realtime_count = 0;
		$realtime_data = get_option( 'tmsm-aquatonic-attendance-data' );
		foreach($realtime_data as $camera){
			if($camera->camera_name === 'bassin'){
				$realtime_count = $camera->number;
			}
		}
		return $realtime_count;
	}

	/**
	 * Dashboard calculate data for big and mini dashboard
	 */
	public function dashboard_calculate_data(){

		$bookings_of_the_day = $this->bookings_of_the_day();

		$now              = new Datetime;
		$minidashboard    = array();
		$dashboard    = array();
		$canstart         = 200; // Fake number of persons that can start, high on purpose
		$canstart_counter = null;
		$history_item = [];

		$second = $now->format( "s" );
		if ( $second > 0 ) {
			$now->add( new DateInterval( "PT" . ( 60 - $second ) . "S" ) );
		}
		$minute = $now->format( "i" );
		$minute = $minute % 15;
		if ( $minute != 0 ) {
			// Count difference
			$diff = 15 - $minute;
			// Add difference
			$now->add( new DateInterval( "PT" . $diff . "M" ) );
			$now->modify( '-15 minutes' );
		}

		$averagecourse = $this->get_option('courseaverage');


		$interval               = DateInterval::createFromDateString( $this->slotsize_minutes() . ' minutes' );
		$now_plus_courseaverage = clone $now;
		$now_plus_courseaverage->modify( '+' . $averagecourse . ' minutes' );
		$period = new DatePeriod( $now, $interval, $now_plus_courseaverage );

		// For Tests
		$now_for_testing_lessons = clone $now;
		$period_for_testing_lessons = clone $period;
		if( ! empty($this->get_option('tests_lessonsdate')) ){
			$now_for_testing_lessons_year = substr($this->get_option('tests_lessonsdate'), 0, 4);
			$now_for_testing_lessons_month = substr($this->get_option('tests_lessonsdate'), 4, 2);
			$now_for_testing_lessons_day = substr($this->get_option('tests_lessonsdate'), 6, 2);
			$now_for_testing_lessons->setDate($now_for_testing_lessons_year, $now_for_testing_lessons_month, $now_for_testing_lessons_day);
			$now_for_testing_lessons_plus_courseaverage = clone $now_for_testing_lessons;
			$now_for_testing_lessons_plus_courseaverage->modify( '+' . $averagecourse . ' minutes' );
			$period_for_testing_lessons = new DatePeriod( $now_for_testing_lessons, $interval, $now_for_testing_lessons_plus_courseaverage );
		}

		$plugin_public                 = new Tmsm_Aquatonic_Course_Booking_Public( $this->plugin_name, null );

		//echo '<pre>';
		$capacity_timeslots_forthedate = $plugin_public->capacity_timeslots_forthedate( date( 'Y-m-d' ) );
		$allotment_timeslots_forthedate = $plugin_public->allotment_timeslots_forthedate( date( 'Y-m-d' ) );
		$treatments_timeslots_forthedate = $plugin_public->treatments_capacity_timeslots_forthedate( date( 'Y-m-d' ) );

		//print_r( allotment_timeslots_forthedate );
		//echo '</pre>';

		$realtime = self::get_attendance_realtime_count();

		$site_id = (int) $this->get_option('aquos_siteid');

		// Temp fix for Saint-Malo camera wrong realtime data on 2021-08-05
		if ( $site_id === 1 && date( 'Y-m-d' ) === '2021-08-05' ) {
			$realtime += 32;
		}
		// Temp fix for Rennes camera wrong realtime data on 2021-10-12
		if ( $site_id === 0 && date( 'Y-m-d' ) === '2021-10-12' ) {
			$realtime += 8;
		}
		// Temp fix for Saint-Malo camera wrong realtime data on 2021-10-20
		if ( $site_id === 1 && date( 'Y-m-d' ) === '2021-10-20' ) {
			$realtime += 35;
		}
		// Temp fix for Paris camera wrong realtime data on 2022-03-02
		if ( $site_id === 2 && date( 'Y-m-d' ) === '2022-03-02' ) {
			$realtime += 37;
		}

		$realtime = max( $realtime, 0 );
		if ( ! empty( $this->get_option( 'tests_realtimeattendance' ) ) ) {
			$realtime = $this->get_option( 'tests_realtimeattendance' );
		}


		$lessons_data = self::lessons_get_data();

		$dashboard[0][] = '';

		$counter = 0;
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;

			$date                              = wp_date( get_option( 'time_format' ), $period_item->getTimestamp() );
			$minidashboard[ $counter ]['date'] = $date;


			$dashboard[0][] = $date;
		}


		// Attendance Capacity

		$dashboard[1][] = __( 'Capacity', 'tmsm-aquatonic-course-booking' );

		$counter                                  = 0;
		$capacity_timeslots_forthedate_counter    = [];
		$capacity_timeslots_forthedate_difference = [];
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;

			$cell = '';
			if ( ! isset( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
				$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
			}
			$cell                                              .= '<span class="capacity capacity-' . $counter . '">'
			                                                      . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
			                                                      . '</span>';
			$capacity_timeslots_forthedate_counter[ $counter ] = $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];

			if ( $counter != 1
			     && $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] != $capacity_timeslots_forthedate_counter[ $counter
			                                                                                                                           - 1 ] ) {
				$difference = ( ( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
				                  - $capacity_timeslots_forthedate_counter[ $counter - 1 ] ) >= 0 ? '+' : '' )
				              . ( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
				                  - $capacity_timeslots_forthedate_counter[ $counter - 1 ] );

				$cell                                                 .= ' (<span class="capacity-different capacity-different-' . $counter . '">'
				                                                         . $difference . '</span>)';
				$capacity_timeslots_forthedate_difference[ $counter ] = $difference;

			}

			$dashboard[1][] = $cell;

		}

		// Lessons Registered
		if ( $this->lessons_has_data() ) {

			$dashboard[30][] = __( 'Registered Lesson Members', 'tmsm-aquatonic-course-booking' );

			$counter                               = 0;
			$lessons_registered_forthedate_counter = [];
			$period_for_lessons                    = ( $period_for_testing_lessons !== $period ? $period_for_testing_lessons : $period );

			foreach ( $period_for_lessons as $period_item ) {
				$period_item->setTimezone( wp_timezone() );
				$counter ++;
				$lessons_registered_forthedate_counter[ $counter ] = $this->lessons_registered_forthetime( $period_item );


				$cell = '';
				$cell .= '<span class="tooltip-trigger lessons-registered lessons-registered-' . $counter . '">'
				         . $lessons_registered_forthedate_counter[ $counter ] . '</span>';

				if ( $period_for_testing_lessons != $period ) {
					$cell .= ' ';
					$cell .= __( '(Test Mode)', 'tmsm-aquatonic-course-booking' );
				}

				$dashboard[30][] = $cell;

			}
		}

		// Lessons Ending
		if ( $this->lessons_has_data() ) {

			$dashboard[31][] = __( 'Ending Lesson Members', 'tmsm-aquatonic-course-booking' );

			$counter                               = 0;
			$lessons_ending_forthedate_counter = [];
			$period_for_lessons                    = ( $period_for_testing_lessons !== $period ? $period_for_testing_lessons : $period );

			foreach ( $period_for_lessons as $period_item ) {
				$period_item->setTimezone( wp_timezone() );
				$counter ++;
				$lessons_ending_forthedate_counter[ $counter ] = $this->lessons_ending_forthetime( $period_item );


				$cell = '';
				$cell .= '<span class="tooltip-trigger lessons-ending lessons-ending-' . $counter . '">'
				         . $lessons_ending_forthedate_counter[ $counter ] . '</span>';

				if ( $period_for_testing_lessons != $period ) {
					$cell .= ' ';
					$cell .= __( '(Test Mode)', 'tmsm-aquatonic-course-booking' );
				}

				$dashboard[31][] = $cell;

			}
		}

		// Lessons Starting
		if ( $this->lessons_has_data() ) {

			$dashboard[32][] = __( 'Starting Lesson Members', 'tmsm-aquatonic-course-booking' );

			$counter                               = 0;
			$lessons_starting_forthedate_counter = [];
			$period_for_lessons                    = ( $period_for_testing_lessons !== $period ? $period_for_testing_lessons : $period );

			foreach ( $period_for_lessons as $period_item ) {
				$period_item->setTimezone( wp_timezone() );
				$counter ++;
				$lessons_starting_forthedate_counter[ $counter ] = $this->lessons_starting_forthetime( $period_item );


				$cell = '';
				$cell .= '<span class="tooltip-trigger lessons-starting lessons-starting-' . $counter . '">'
				         . $lessons_starting_forthedate_counter[ $counter ] . '</span>';

				if ( $period_for_testing_lessons != $period ) {
					$cell .= ' ';
					$cell .= __( '(Test Mode)', 'tmsm-aquatonic-course-booking' );
				}

				$dashboard[32][] = $cell;

			}
		}

		// Lessons Arrived
		/*if ( $this->lessons_has_data() ) {

			$dashboard[3][] = __( 'Arrived Lesson Members', 'tmsm-aquatonic-course-booking' );

			$counter            = 0;
			$period_for_lessons = ( $period_for_testing_lessons !== $period ? $period_for_testing_lessons : $period );

			foreach ( $period_for_lessons as $period_item ) {
				$period_item->setTimezone( wp_timezone() );
				$counter ++;
				$cell = '';

				$cell .= '<span class="tooltip-trigger lessons-arrived lessons-arrived-' . $counter . '">'
				         . $this->lessons_arrived_forthetime( $period_item ) . '</span>';
				if ( $period_for_testing_lessons !== $period ) {
					$cell .= ' ';
					$cell .= __( '(Test Mode)', 'tmsm-aquatonic-course-booking' );
				}
				$dashboard[3][] = $cell;

			}

		}*/

		// Treatments Capacity
		if ( ! empty( $treatments_timeslots_forthedate ) ) {

			$dashboard[40][] = __( 'Treatment+Course Allotment', 'tmsm-aquatonic-course-booking' );

			$counter                                    = 0;
			$treatments_timeslots_forthedate_counter    = [];
			$treatments_timeslots_forthedate_difference = [];
			foreach ( $period as $period_item ) {
				$period_item->setTimezone( wp_timezone() );
				$counter ++;
				$cell = '';

				if ( ! isset( $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
					$treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
				}
				$cell .= '<span class="treatment treatment-' . $counter . '">'
				         . $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>';

				$treatments_timeslots_forthedate_counter[ $counter ] = $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];

				if ( $counter != 1
				     && $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
				        != $treatments_timeslots_forthedate_counter[ $counter - 1 ] ) {
					$difference = ( ( $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
					                  - $treatments_timeslots_forthedate_counter[ $counter - 1 ] ) >= 0 ? '+' : '' )
					              . ( $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
					                  - $treatments_timeslots_forthedate_counter[ $counter - 1 ] );

					$cell                                                   .= ' (<span class="treatment-different treatment-different-' . $counter
					                                                           . '">' . $difference . '</span>)';
					$treatments_timeslots_forthedate_difference[ $counter ] = $difference;

				}

				$dashboard[40][] = $cell;

			}

		}
		// Booking Allotments

		$dashboard[50][] = __( 'Booking Allotments', 'tmsm-aquatonic-course-booking' );

		$counter                                   = 0;
		$allotment_timeslots_forthedate_counter    = [];
		$allotment_timeslots_forthedate_difference = [];
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;

			$cell = '';

			if ( isset( $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
				$cell .= '<span class="allotment allotment-' . $counter . '">'
				         . $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>';
			} else {
				$allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
			}
			$allotment_timeslots_forthedate_counter[ $counter ] = $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];
			if($counter ===1){
				$history_item['datetime'] = $period_item->format( 'Y-m-d H:i:s' );
				$history_item['courseallotment'] = $allotment_timeslots_forthedate_counter[ $counter ];
			}

			if ( $counter != 1
			     && $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] != $allotment_timeslots_forthedate_counter[ $counter - 1 ] ) {
				$difference = ( ( $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
				                  - $allotment_timeslots_forthedate_counter[ $counter - 1 ] ) >= 0 ? '+' : '' )
				              . ( $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
				                  - $allotment_timeslots_forthedate_counter[ $counter - 1 ] );

				$cell                                                  .= ' (<span class="allotment-different allotment-different-' . $counter . '">'
				                                                          . $difference . '</span>)';
				$allotment_timeslots_forthedate_difference[ $counter ] = $difference;

			}
			$dashboard[50][] = $cell;

		}

		// Ongoing Bookings

		$dashboard[60][] = __( 'Ongoing Bookings', 'tmsm-aquatonic-course-booking' );


		$counter = 0;
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;

			$cell = '';

			$cell .= '<span class="tooltip-trigger booking-ongoing booking-ongoing-' . $counter . '">'
			         . $plugin_public->get_participants_ongoing_forthetime( $period_item ) . '</span>';
			if($counter === 1){
				$history_item['ongoingbookings'] = $plugin_public->get_participants_ongoing_forthetime( $period_item );
			}

			$bookings_inside = '';
			foreach ( $bookings_of_the_day as $booking ) {
				if ( in_array( $booking->status, [ 'active', 'arrived' ] ) && $booking->course_start <= $period_item->format( 'Y-m-d H:i:s' )
				     && $period_item->format( 'Y-m-d H:i:s' ) <= $booking->course_end ) {
					$bookings_inside .= '' . $booking->firstname . ' ' . $booking->lastname . ' x' . $booking->participants . '<br>';
				}
			}
			if ( ! empty( $bookings_inside ) ) {
				$bookings_inside = '<div class="tooltip-content">' . $bookings_inside . '</div';
			}
			$cell .= $bookings_inside;

			$dashboard[60][] = $cell;

		}

		// Ending Bookings

		$dashboard[61][] = __( 'Ending Bookings', 'tmsm-aquatonic-course-booking' );


		$counter = 0;
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;
			$cell = '';


			$cell .= '<span class="tooltip-trigger booking-ending booking-ending-' . $counter . '">'
			         . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>';

			$bookings_inside = '';
			foreach ( $bookings_of_the_day as $booking ) {
				if ( in_array( $booking->status, [ 'active', 'arrived' ] ) && $period_item->format( 'Y-m-d H:i:s' ) == $booking->course_end ) {
					$bookings_inside .= '' . $booking->firstname . ' ' . $booking->lastname . ' x' . $booking->participants . '<br>';
				}
			}
			if ( ! empty( $bookings_inside ) ) {
				$bookings_inside = '<div class="tooltip-content">' . $bookings_inside . '</div';
			}
			$cell .= $bookings_inside;

			$dashboard[61][] = $cell;


		}

		// Starting Bookings

		$dashboard[62][] = __( 'Starting Bookings', 'tmsm-aquatonic-course-booking' );

		$counter = 0;
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;

			$cell = '';


			$cell .= '<span class="tooltip-trigger booking-starting booking-starting-' . $counter . '">'
			         . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>';

			$bookings_inside = '';
			foreach ( $bookings_of_the_day as $booking ) {
				if ( in_array( $booking->status, [ 'active', 'arrived' ] ) && $booking->course_start == $period_item->format( 'Y-m-d H:i:s' ) ) {
					$bookings_inside .= '' . $booking->firstname . ' ' . $booking->lastname . ' x' . $booking->participants . '<br>';
				}
			}
			if ( ! empty( $bookings_inside ) ) {
				$bookings_inside = '<div class="tooltip-content">' . $bookings_inside . '</div';
			}
			$cell .= $bookings_inside;

			$dashboard[62][] = $cell;

		}

		// Free (old alternative)

		/*$dashboard[70][] = __( 'Free', 'tmsm-aquatonic-course-booking' );


		$counter = 0;
		$free    = [];
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;
			$cell = '';


			// First "Free" column
			if ( $counter === 1 ) {
				$free[ $counter ] = (
					$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
					- $realtime
					//+ $plugin_public->get_participants_ending_forthetime( $period_item )
					//- $plugin_public->get_participants_starting_forthetime( $period_item )
				);
				$cell             .= '<span class="free free-' . $counter . '">'
				                     . $free[ $counter ]
				                     . '</span>';

				$cell .= ' <span class="onlyadmin">('
				         . '<span class="capacity capacity-' . $counter . '">'
				         . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>'
				         . '-' . '<span class="realtime">' . $realtime . '</span>'
				         //. '+' . '<span class="booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>'
				         //. '-' . '<span class="booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
				         . ')</span>';

			} // Other "Free" columns
			else {

				$free[ $counter ] = ( $free[ ( $counter - 1 ) ]
				                      + $plugin_public->get_participants_ending_forthetime( $period_item )
				                      - $plugin_public->get_participants_starting_forthetime( $period_item )
				                      + ( $capacity_timeslots_forthedate_difference[ $counter ] ?? 0 )
				                      + ( $this->lessons_has_data() ? $lessons_registered_forthedate_counter[ ( $counter - 1 ) ] : 0 )
				                      - ( $this->lessons_has_data() ? $lessons_registered_forthedate_counter[ $counter ] : 0 )
				                      + ( ! empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter - 1 ] : 0 )
				                      - ( ! empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter ] : 0 )
				);
				$cell             .= '<span class="free free-' . $counter . '">'
				                     . $free[ $counter ] . '</span>';

				$cell .= ' <span class="onlyadmin">('
				         . '<span class="free free-' . ( $counter - 1 ) . '">'
				         . $free[ ( $counter - 1 ) ]
				         . '</span>'
				         . '+'
				         . '<span class="booking-ending booking-ending-' . $counter . '">'
				         . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>' . '-'
				         . '<span class="booking-starting booking-starting-' . $counter . '">'
				         . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
				         . ( isset( $capacity_timeslots_forthedate_difference[ $counter ] )
						? '<span class="capacity-different capacity-different-' . $counter . '">'
						  . $capacity_timeslots_forthedate_difference[ $counter ] . '</span>' : '' )
				         . ( $this->lessons_has_data() ? '+' . '<span class="lessons-registered lessons-registered-'
				                                         . ( $counter - 1 ) . '">'
				                                         . $lessons_registered_forthedate_counter[ ( $counter - 1 ) ]
				                                         . '</span>' : '' )
				         . ( $this->lessons_has_data() ? '-' . '<span class="lessons-registered lessons-registered-'
				                                         . $counter . '">'
				                                         . $lessons_registered_forthedate_counter[ $counter ] . '</span>'
						: '' )

				         . ( ! empty( $treatments_timeslots_forthedate ) ? '+' . '<span class="treatment treatment-'
				                                                           . ( $counter - 1 ) . '">'
				                                                           . $treatments_timeslots_forthedate_counter[ $counter - 1 ] . '</span>'
						: '' )
				         . ( ! empty( $treatments_timeslots_forthedate ) ? '-' . '<span class="treatment treatment-'
				                                                           . $counter . '">' . $treatments_timeslots_forthedate_counter[ $counter ]
				                                                           . '</span>' : '' )


				         . ')</span>';

			}

			$minidashboard[ $counter ]['free'] = $free[ $counter ];

			if ( $free[ $counter ] < $canstart ) {
				$canstart_counter = $counter;
			}

			$canstart                 = min( $canstart, $free[ $counter ] );
			$minidashboard[0]['date'] = esc_html__( 'Can Start', 'tmsm-aquatonic-course-booking' );
			$minidashboard[0]['free'] = $canstart;

			$dashboard[70][] = $cell;


		}*/

		// Free alternative 1 (now default)

		$dashboard[81][] = __( 'Free', 'tmsm-aquatonic-course-booking' );

		$counter = 0;
		$free_alternative1    = [];
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;
			$cell = '';

			// First "Free" column
			if ( $counter === 1 ) {

				$free_alternative1[ $counter ] = (
					$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
					- $realtime
					+ $plugin_public->get_participants_ending_forthetime( $period_item )
					- $plugin_public->get_participants_starting_forthetime( $period_item )
				);
				$history_item['free'] = $free_alternative1[ $counter ];

				// Force value between 0 and capacity
				$free_alternative1[ $counter ] = min($free_alternative1[ $counter ], $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]);
				$free_alternative1[ $counter ] = max(0, $free_alternative1[ $counter ]);

				$cell             .= '<span class="free free-' . $counter . '">'
				                     . $free_alternative1[ $counter ]
				                     . '</span>';

				$cell .= ' <span class="onlyadmin">('
				         . '<span class="capacity capacity-' . $counter . '">'
				         . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>'
				         . '-' . '<span class="realtime">' . $realtime . '</span>'
				         . '+' . '<span class="booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>'
				         . '-' . '<span class="booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
				         . ')</span>';

			} // Other "Free" columns
			else {

				$free_alternative1[ $counter ] = ( $free_alternative1[ ( $counter - 1 ) ]
				                      + $plugin_public->get_participants_ending_forthetime( $period_item )
				                      - $plugin_public->get_participants_starting_forthetime( $period_item )
				                      + ( $capacity_timeslots_forthedate_difference[ $counter ] ?? 0 )
				                      + ( $this->lessons_has_data() ? $lessons_registered_forthedate_counter[ ( $counter - 1 ) ] : 0 )
				                      - ( $this->lessons_has_data() ? $lessons_registered_forthedate_counter[ $counter ] : 0 )
				                      + ( ! empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter - 1 ] : 0 )
				                      - ( ! empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter ] : 0 )
				);

				// Force value between 0 and capacity
				$free_alternative1[ $counter ] = min($free_alternative1[ $counter ], ( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ?? 0 ));
				$free_alternative1[ $counter ] = max(0, $free_alternative1[ $counter ]);

				$cell             .= '<span class="free free-' . $counter . '">'
				                     . $free_alternative1[ $counter ] . '</span>';

				$cell .= ' <span class="onlyadmin">('
				         . '<span class="free free-' . ( $counter - 1 ) . '">'
				         . $free_alternative1[ ( $counter - 1 ) ]
				         . '</span>'
				         . '+'
				         . '<span class="booking-ending booking-ending-' . $counter . '">'
				         . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>' . '-'
				         . '<span class="booking-starting booking-starting-' . $counter . '">'
				         . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
				         . ( isset( $capacity_timeslots_forthedate_difference[ $counter ] )
						? '<span class="capacity-different capacity-different-' . $counter . '">'
						  . $capacity_timeslots_forthedate_difference[ $counter ] . '</span>' : '' )
				         . ( $this->lessons_has_data() ? '+' . '<span class="lessons-registered lessons-registered-'
				                                         . ( $counter - 1 ) . '">'
				                                         . $lessons_registered_forthedate_counter[ ( $counter - 1 ) ]
				                                         . '</span>' : '' )
				         . ( $this->lessons_has_data() ? '-' . '<span class="lessons-registered lessons-registered-'
				                                         . $counter . '">'
				                                         . $lessons_registered_forthedate_counter[ $counter ] . '</span>'
						: '' )

				         . ( ! empty( $treatments_timeslots_forthedate ) ? '+' . '<span class="treatment treatment-'
				                                                           . ( $counter - 1 ) . '">'
				                                                           . $treatments_timeslots_forthedate_counter[ $counter - 1 ] . '</span>'
						: '' )
				         . ( ! empty( $treatments_timeslots_forthedate ) ? '-' . '<span class="treatment treatment-'
				                                                           . $counter . '">' . $treatments_timeslots_forthedate_counter[ $counter ]
				                                                           . '</span>' : '' )


				         . ')</span>';

			}

			$minidashboard[ $counter ]['free'] = $free_alternative1[ $counter ];

			if ( $free_alternative1[ $counter ] < $canstart ) {
				$canstart_counter = $counter;
			}

			$canstart                 = min( $canstart, $free_alternative1[ $counter ] );
			$history_item['canstart'] = $canstart;

			$dashboard[81][] = $cell;


		}

		// Free alternative 2

		/*
		$dashboard[82][] = __( 'Free (alternative 2)', 'tmsm-aquatonic-course-booking' );


		$counter = 0;
		$free_alternative2    = [];
		foreach ( $period as $period_item ) {
			$period_item->setTimezone( wp_timezone() );
			$counter ++;
			$cell = '';


			// First "Free" column
			if ( $counter === 1 ) {
				$free_alternative2[ $counter ] = (
					$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
					- $realtime
					+ $plugin_public->get_participants_ending_forthetime( $period_item )
					- $plugin_public->get_participants_starting_forthetime( $period_item )
					+ ( $this->lessons_has_data() ? $lessons_ending_forthedate_counter[ ( $counter ) ] : 0 )
					- ( $this->lessons_has_data() ? $lessons_starting_forthedate_counter[ ( $counter ) ] : 0 )
				);
				$cell             .= '<span class="free free-' . $counter . '">'
				                     . $free_alternative2[ $counter ]
				                     . '</span>';

				$cell .= ' <span class="onlyadmin">('
				         . '<span class="capacity capacity-' . $counter . '">'
				         . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>'
				         . '-' . '<span class="realtime">' . $realtime . '</span>'
				         . '+' . '<span class="booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>'
				         . '-' . '<span class="booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
				         . '+' . '<span class="lesson-ending lesson-ending-' . $counter .'">' . ( $this->lessons_has_data() ? $lessons_ending_forthedate_counter[ ( $counter ) ] : 0 ) . '</span>'
				         . '-' . '<span class="lesson-starting lesson-starting-' . $counter .'">' . ( $this->lessons_has_data() ? $lessons_starting_forthedate_counter[ ( $counter ) ] : 0 ) . '</span>'
				         . ')</span>';

			} // Other "Free" columns
			else {

				$free_alternative2[ $counter ] = ( $free_alternative2[ ( $counter - 1 ) ]
				                      + $plugin_public->get_participants_ending_forthetime( $period_item )
				                      - $plugin_public->get_participants_starting_forthetime( $period_item )
				                      + ( $capacity_timeslots_forthedate_difference[ $counter ] ?? 0 )
                                      + ( $this->lessons_has_data() ? $lessons_ending_forthedate_counter[ ( $counter ) ] : 0 )
                                      - ( $this->lessons_has_data() ? $lessons_starting_forthedate_counter[ ( $counter ) ] : 0 )
				                      + ( ! empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter - 1 ] : 0 )
				                      - ( ! empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter ] : 0 )
				);
				$cell             .= '<span class="free free-' . $counter . '">'
				                     . $free_alternative2[ $counter ] . '</span>';

				$cell .= ' <span class="onlyadmin">('
				         . '<span class="free free-' . ( $counter - 1 ) . '">'
				         . $free_alternative2[ ( $counter - 1 ) ]
				         . '</span>'
				         . '+'
				         . '<span class="booking-ending booking-ending-' . $counter . '">'
				         . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>' . '-'
				         . '<span class="booking-starting booking-starting-' . $counter . '">'
				         . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
				         . ( isset( $capacity_timeslots_forthedate_difference[ $counter ] )
						? '<span class="capacity-different capacity-different-' . $counter . '">'
						  . $capacity_timeslots_forthedate_difference[ $counter ] . '</span>' : '' )

				         . '+' . '<span class="lesson-ending lesson-ending-' . $counter .'">' . ( $this->lessons_has_data() ? $lessons_ending_forthedate_counter[ ( $counter ) ] : 0 ) . '</span>'
				         . '-' . '<span class="lesson-starting lesson-starting-' . $counter .'">' . ( $this->lessons_has_data() ? $lessons_starting_forthedate_counter[ ( $counter ) ] : 0 ) . '</span>'


				         . ( ! empty( $treatments_timeslots_forthedate ) ? '+' . '<span class="treatment treatment-'
				                                                           . ( $counter - 1 ) . '">'
				                                                           . $treatments_timeslots_forthedate_counter[ $counter - 1 ] . '</span>'
						: '' )
				         . ( ! empty( $treatments_timeslots_forthedate ) ? '-' . '<span class="treatment treatment-'
				                                                           . $counter . '">' . $treatments_timeslots_forthedate_counter[ $counter ]
				                                                           . '</span>' : '' )


				         . ')</span>';

			}

			//$minidashboard[ $counter ]['free_alternative'] = $free_alternative2[ $counter ];

			if ( $free_alternative2[ $counter ] < $canstart ) {
				//$canstart_counter = $counter;
			}

			//$canstart                 = min( $canstart, $free_alternative2[ $counter ] );

			$dashboard[82][] = $cell;



		}*/

		// Can Start

		$dashboard[90][] = __( 'Can Start', 'tmsm-aquatonic-course-booking' );

		$cell = '<span class="free free-' . $canstart_counter . '">' . $canstart . '</span>';
		$dashboard[90][] = $cell;

		$dashboard[90][] = __( '(Minimum Value of Free Row)', 'tmsm-aquatonic-course-booking' );
		$dashboard[90][] = ' ';
		$dashboard[90][] = ' ';
		$dashboard[90][] = ' ';
		$dashboard[90][] = ' ';
		$minidashboard[ 0 ]['date'] = esc_html__( 'Can Start', 'tmsm-aquatonic-course-booking' );;
		$minidashboard[ 0 ]['free'] = $canstart;


		// Real Time

		$dashboard[100][] = __( 'Real Time', 'tmsm-aquatonic-course-booking' );

		$cell = '';
		$cell .= '<span class="realtime">' . $realtime . '</span>';
		if ( ! empty( $this->get_option( 'tests_realtimeattendance' ) ) && $this->get_option( 'tests_realtimeattendance' ) != 0 ) {
			$cell .= __( '(Test Mode)', 'tmsm-aquatonic-course-booking' );
		}
		if ( empty( $realtime ) ) {
			$cell = __( 'Real time data is missing!', 'tmsm-aquatonic-course-booking' );
		}
		$history_item['realtime'] = $realtime ?? 0;

		$dashboard[100][] = $cell;
		$dashboard[100][] = '';
		$dashboard[100][] = '';
		$dashboard[100][] = '';
		$dashboard[100][] = '';
		$dashboard[100][] = '';

		// Save History Item
		if( get_option('tmsm-aquatonic-course-booking-db-version') == TMSM_AQUATONIC_COURSE_BOOKING_DB_VERSION){
			self::dashboard_save_history_item($history_item);
		}
		else{
			// Update database schema before adding history items in new table
			require_once TMSM_AQUATONIC_COURSE_BOOKING_PATH . 'includes/class-tmsm-aquatonic-course-booking-activator.php';
			Tmsm_Aquatonic_Course_Booking_Activator::create_database_schema();
		}

		// Save Dashboard data for use in the Dashboard
		update_option('tmsm-aquatonic-course-booking-minidashboard', $minidashboard);
		update_option('tmsm-aquatonic-course-booking-dashboard', $dashboard);

	}

	/**
	 * Save history itme to history table
	 *
	 * @param $history_item
	 *
	 * @return bool|void
	 */
	function dashboard_save_history_item($history_item){
		global $wpdb;

		$table = $wpdb->prefix . 'aquatonic_course_history';

		// Don't store in history if course is not opened
		if( $history_item['courseallotment'] == 0){
			return;
		}

		// Preapre data
		$data = array(
			'datetime'        => $history_item['datetime'],
			'courseallotment' => $history_item['courseallotment'],
			'ongoingbookings' => $history_item['ongoingbookings'],
			'canstart'        => $history_item['canstart'],
			'realtime'        => $history_item['realtime'],
		);
		$format = array(
			'%s',
			'%d',
			'%d',
			'%d',
			'%d',
		);

		// Does this datetime already exist?
		$field_name = 'datetime';
		$prepared_statement = $wpdb->prepare( "SELECT {$field_name} FROM {$table} WHERE  {$field_name} = %s", $history_item['datetime'] );
		$datetimes_found = $wpdb->get_col( $prepared_statement );
		$datetime_found = null;
		if( ! empty($datetimes_found)){
			$datetime_found = $datetimes_found[0] ?? null;
		}
		if( ! empty( $datetime_found ) ){
			// Update data into custom table
			$where = [$field_name => $datetime_found];
			$result = $wpdb->update( $table, $data, $where, $format );
			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('History Item updated result: ' . $result);
			}
		}
		else{
			// Insert data into custom table
			$result = $wpdb->insert( $table, $data, $format );
			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('History Item inserted result: ' . $result);
			}
		}

		// Log last query error
		if( $result == 0 && $wpdb->last_error){
			error_log('History Item Last Error: ' . $wpdb->last_error);
		}

		return true;
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
	 * Send Contacts to Aquos every 5 minutes
	 */
	public function aquos_send_contacts_cron() {

		global $wpdb;

		//error_log( 'aquos_send_contacts_cron_start ' . home_url() );

		$lastexec_option_name = 'tmsm-aquatonic-course-booking-aquos-send-contacts-last-exec';
		$now = time();
		$lastexec_timestamp = get_option( $lastexec_option_name );

		if ( empty( $lastexec_timestamp ) ) {
			$lastexec_timestamp = $now;
			update_option( $lastexec_option_name, $lastexec_timestamp, true );
		}

		$lastexec_object = new DateTime();
		$lastexec_object->setTimezone(wp_timezone());
		$lastexec_object->setTimestamp($lastexec_timestamp);
		$now_object = new DateTime();
		$now_object->setTimezone(wp_timezone());
		$now_object->setTimestamp($now);

		$bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE date_created > '%s' AND date_created <= '%s'", $lastexec_object->format('Y-m-d H:i:s'), $now_object->format('Y-m-d H:i:s') ), ARRAY_A );

		//error_log('aquos_found '. count($bookings) . ' bookings between ' . $lastexec_object->format('Y-m-d H:i:s'). ' and '. $now_object->format('Y-m-d H:i:s'));

		foreach($bookings as $booking){

			$this->aquos_send_contact($booking);
        }

		$lastexec_timestamp = $now;
		update_option( $lastexec_option_name, $lastexec_timestamp, true );
		//error_log('aquos_send_contacts_cron_end');
	}


	/**
	 * Send contact data to Aquos
	 *
	 * @param $booking
	 */
	public function aquos_send_contact( $booking ){

		//error_log('aquos_send_contact_start');

		// Aquos, send contact information
		$endpoint = $this->get_option('aquos_endpoint_contact');
		$site_id = (int) $this->get_option('aquos_siteid');


		if ( ! empty ( $endpoint ) && is_int( $site_id ) && ! empty( $booking['email'] ) ) {
			$data = [
				'civilite'      => ( $booking['title'] == 1 ? 'M.' : 'Mme' ),
				'prenom'        => $booking['firstname'],
				'nom'           => $booking['lastname'],
				'email'         => $booking['email'],
				'datenaissance' => str_replace( '-', '', $booking['birthdate'] ),
				'telephone'     => $booking['phone'],
				'id_site'       => (string) $site_id,
			];

			// Default status
			$status = 'sent';

			$body = json_encode($data);

			$headers = [
				'Content-Type' => 'application/json; charset=utf-8',
				'X-Signature' => $this->aquos_generate_signature( $body ),
				'Cache-Control' => 'no-cache',
			];

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('headers with signature:');
				error_log(print_r($headers, true));

				error_log('body:');
				error_log($body);

			}

			$response = wp_remote_post(
				$endpoint,
				array(
					'headers'     => $headers,
					'body'        => $body,
					'timeout'     => 30,
					'data_format' => 'body',
					'sslverify'   => false,
				)
			);

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('Aquos response:');
				error_log(print_r($response, true));
				error_log('wp_remote_retrieve_body( $response ): ' );
				error_log(print_r(wp_remote_retrieve_body( $response ), true));
				error_log(print_r($response_data, true));
				error_log('$response_data->Status: ' .$response_data->Status);
				error_log('$response_data->Error: ' .$response_data->Error);
			}

			if ( $response_code >= 400 ) {

				error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquatonic-course-booking' ),
					absint( $response_code ) ) );
				$status =  sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquatonic-course-booking' ),
					absint( $response_code ) );

			}

			if ( isset($response_data->Status) &&  $response_data->Status === 'false') {

				error_log(sprintf( __( 'Error message: %s', 'tmsm-aquatonic-course-booking' ), $response_data->Error ));
				$status = sprintf( __( 'Error message: %s', 'tmsm-aquatonic-course-booking' ), $response_data->Error );

			}

			if ( is_wp_error( $response ) ) {

				error_log('Error message: '. $response->get_error_message());
				$status = 'is_wp_error ' . $response->get_error_message();

			}

			if ( isset($response_data->Status) &&  $response_data->Status === 'true') {
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log('aquos_contact_inserted '. $booking['firstname']. ' ' . $booking['lastname']);
				}
			}

			// Storing Aquos status in entry meta
			$token = $booking['token'];
			if( ! empty($token)){
				$entry = self::find_entry_with_token($token);
				if( ! empty( $entry )){
					$entry_id = $entry['id'];
					if( !empty($entry_id)){
						gform_update_meta( $entry_id, '_aquos_status', $status );
					}
					else{
						error_log('entry_id not set'  . $token);
						error_log(print_r( $entry, true));
					}
				}else{
					error_log('entry not found for token'  . $token);
				}

			}

		} // endpoint exists
	}

	/**
	 * Send arrived contacts to Customer Alliance every 5 minutes
	 */
	public function customeralliance_send_contacts_cron() {

		global $wpdb;

		error_log( 'customeralliance_send_contacts_cron_start ' . home_url() );

		$lastexec_option_name = 'tmsm-aquatonic-course-booking-customeralliance-send-contacts-last-exec';
		$now = time();
		$lastexec_timestamp = get_option( $lastexec_option_name );

		if ( empty( $lastexec_timestamp ) ) {
			$lastexec_timestamp = $now;
			update_option( $lastexec_option_name, $lastexec_timestamp, true );
		}

		$lastexec_object = new DateTime();
		$lastexec_object->setTimezone(wp_timezone());
		$lastexec_object->setTimestamp($lastexec_timestamp);

		$now_object = new DateTime();
		$now_object->setTimezone(wp_timezone());
		$now_object->setTimestamp($now);

		// Get bookings arrived from yesterday (to avoid missing customer arriving late)
		$lastexec_object->modify('-1 day');
		$now_object->modify('-1 day');

		// Get only arrived customers based on course date
		$bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE status= 'arrived' AND course_start > '%s' AND course_start <= '%s'", $lastexec_object->format('Y-m-d H:i:s'), $now_object->format('Y-m-d H:i:s') ), ARRAY_A );

		error_log('found '. count($bookings) . ' bookings between ' . $lastexec_object->format('Y-m-d H:i:s'). ' and '. $now_object->format('Y-m-d H:i:s'));

		foreach($bookings as $booking){
			$this->customeralliance_send_contact($booking);
        }

		$lastexec_timestamp = $now;
		update_option( $lastexec_option_name, $lastexec_timestamp, true );
		error_log('customeralliance_send_contacts_cron_end');
	}


	/**
	 * Send contact data to Customer Alliance
	 *
	 * @param $booking
	 *
	 * @return void
	 */
	public function customeralliance_send_contact( $booking ) {

		error_log( 'customeralliance_send_contact_start' );

		$endpoint       = 'https://interfaces.customer-alliance.com/api/';
		$access_key     = $this->get_option( 'customeralliance_accesskey' );
		$review_subject = $this->get_option( 'customeralliance_reviewsubject' );

		if ( ! empty ( $access_key ) && ! empty ( $review_subject ) && ! empty( $booking['email'] ) ) {
			$endpoint     .= $access_key . '/' . $review_subject;
			$phone_number = $booking['phone'];

			// Format phone number in E.164 standard (+33XXXXXX) does it contain the prefix
			if ( strpos( $phone_number, '+33' ) === false && strpos( $phone_number, '033' ) ) {
				// Phone number is OK, do nothing
			} // No country prefix
			else {
				// First characters are 033, remove it and add prefix
				if ( strpos( substr( $phone_number, 0, 3 ), '033' ) !== false ) {
					$phone_number = '+33' . substr( $phone_number, 3 );
				}
				// First character is zero, remove it and add prefix
				if ( strpos( substr( $phone_number, 0, 1 ), '0' ) !== false ) {
					$phone_number = '+33' . substr( $phone_number, 1 );
				}
			}

			// Prepare data for Customer Alliance API
			$data = [
				[
				'gender'           => ( $booking['title'] == 1 ? 'm' : 'f' ),
				'first_name'       => $booking['firstname'],
				'name'             => $booking['lastname'],
				'language'         => 'fr',
				'id'               => $booking['email'],
				'email'            => $booking['email'],
				'guest_profile_id' => $booking['email'],
				'room_number'      => __( 'Aquatonic Course', 'tmsm-aquatonic-course-booking' ),
				'room_category'    => __( 'Aquatonic Course', 'tmsm-aquatonic-course-booking' ),
				'arrival_date'     => substr( $booking['course_start'], 0, 10 ),
				'departure_date'   => substr( $booking['course_start'], 0, 10 ),
				'phone_number'     => $phone_number,
				'reservation_id'   => $booking['barcode'],
				'cancelled'        => false,
			]
			];

			// Default status
			$status = 'sent';

			$body = json_encode( $data );

			$headers = [
				'Content-Type'  => 'application/json; charset=utf-8',
				'Cache-Control' => 'no-cache',
			];

			if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
				error_log( 'headers:' );
				error_log( print_r( $headers, true ) );

				error_log( 'body:' );
				error_log( $body );

			}

			$response = wp_remote_post(
				$endpoint,
				array(
					'headers'     => $headers,
					'body'        => $body,
					'timeout'     => 10,
					'data_format' => 'body',
				)
			);

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
				error_log( 'wp_remote_retrieve_body( $response ): ' );
				error_log( print_r( wp_remote_retrieve_body( $response ), true ) );
				/*error_log( 'Customer Alliance response:' );
				error_log( print_r( $response, true ) );


				error_log( print_r( $response_data, true ) );
				error_log( '$response_data->success: ' . $response_data->success );
				error_log( '$response_data->errors: ' . print_r( $response_data->errors, true ) );*/
			}

			if ( $response_code >= 400 ) {

				error_log( __( 'Error: Delivery URL returned code %s with error %s', 'tmsm-aquatonic-course-booking' ),
					absint( $response_code ), wp_remote_retrieve_body( $response ) );
				$status = sprintf( __( 'Error: Delivery URL returned code %s with error %s', 'tmsm-aquatonic-course-booking' ),
					absint( $response_code ), wp_remote_retrieve_body( $response ) );

			}

			if ( is_wp_error( $response ) ) {
				error_log( 'Error message: ' . $response->get_error_message() );
				$status = 'is_wp_error ' . $response->get_error_message();
			}

			if ( isset( $response_data->success ) && $response_data->success === 'true' ) {
				if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
					error_log( 'customeralliance_contact_inserted ' . $booking['firstname'] . ' ' . $booking['lastname']. ' ' . $booking['barcode'] );
				}
			}

			// Storing Aquos status in entry meta
			$token = $booking['token'];
			if ( ! empty( $token ) ) {
				$entry = self::find_entry_with_token( $token );
				if ( ! empty( $entry ) ) {
					$entry_id = $entry['id'];
					if ( ! empty( $entry_id ) ) {
						gform_update_meta( $entry_id, '_customeralliance_status', $status );
					} else {
						error_log( 'entry_id not set' . $token );
						error_log( print_r( $entry, true ) );
					}
				} else {
					error_log( 'entry not found for token' . $token );
				}
			}

		} // endpoint exists
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
	 * Call "Lessons" web service
	 */
	public function lessons_set_data(){

		if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
			error_log('course lessons_set_data');
		}

		$endpoint = $this->get_option('aquos_endpoint_lessons');

		$site_id = (int) $this->get_option('aquos_siteid');
		$tests_lessonsdate = $this->get_option('tests_lessonsdate');

		if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
			error_log('$tests_lessonsdate:'.$tests_lessonsdate);
			error_log('$endpoint:'.$endpoint);
			error_log('$site_id:'.$site_id);
		}
		if ( ! empty ( $endpoint ) && is_int( $site_id ) ) {
			$data = [
				'date'    => $tests_lessonsdate ?? date( 'Ymd' ),
				'id_site' => $site_id,
			];

			$body = json_encode( $data );

			$headers = [
				'Content-Type' => 'application/json; charset=utf-8',
				'X-Signature'  => $this->aquos_generate_signature( $body ),
			];

			$response      = wp_safe_remote_post(
				$endpoint,
				array(
					'headers'     => $headers,
					'body'        => $body,
					'timeout'     => 20,
					'data_format' => 'body',
				)
			);
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
				//error_log(print_r($response, true));
				error_log(print_r($response_data, true));
			}

			if ( $response_code >= 400 ) {

				if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
					error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquatonic-course-booking' ),
						absint( $response_code ) ) );
				}

			}

			if ( isset( $response_data->Status ) && $response_data->Status === 'false' ) {

				if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
					error_log( sprintf( __( 'Aquos reached, with error message: %s', 'tmsm-aquatonic-course-booking' ), $response_data->Error ) );
				}

			}

			if ( is_wp_error( $response ) ) {

				if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
					error_log( 'Aquos reached, error message: ' . $response->get_error_message() );
				}

			}

			$lessons = [];
			if ( ! empty( $response_data ) ) {

				$averagecourse = $this->get_option('courseaverage');
				$notcompatible_slots = array(
					'05' => '-5',
					'10' => '+5',
					'20' => '-5',
					'25' => '+5',
					'35' => '-5',
					'40' => '+5',
					'50' => '-5',
					'55' => '+5',
				);
				foreach($response_data as $response_lesson){

					$lesson_datetime_object = DateTime::createFromFormat( 'Y-m-d H:i', $response_lesson->dateheure, wp_timezone() );

					$dateheure_every15minutes_minutesonly = substr($response_lesson->dateheure, -2, 2);

					//error_log('response_lesson->dateheure:' . $response_lesson->dateheure);
					//error_log('$dateheure_every15minutes_minutesonly:' . $dateheure_every15minutes_minutesonly);

					if( array_key_exists($dateheure_every15minutes_minutesonly, $notcompatible_slots)){
						//error_log('insidearray:' . $notcompatible_slots[$dateheure_every15minutes_minutesonly]);
						$lesson_datetime_object->modify($notcompatible_slots[$dateheure_every15minutes_minutesonly] . ' minutes');
					}

					if( ! empty( $lesson_datetime_object )) {
						$lesson_datetime_start = clone $lesson_datetime_object;
						$lesson_datetime_start->modify( '-' . $this->get_option( 'lessonbefore' ) . ' minutes' );
						$lesson_datetime_end = clone $lesson_datetime_object;
						$lesson_datetime_end->modify( '+' . $this->get_option( 'lessonafter' ) . ' minutes' )->modify( '+' . $response_lesson->duree . ' minutes' );

						$interval = DateInterval::createFromDateString( $this->slotsize_minutes() . ' minutes' );
						$period   = new DatePeriod( $lesson_datetime_start, $interval, $lesson_datetime_end );

						// Set starting & ending lessons
						if ( isset( $lessons[ $lesson_datetime_start->format( 'Y-m-d H:i:s' ) ] ) ) {
							$lessons[ $lesson_datetime_start->format( 'Y-m-d H:i:s' ) ]['starting'] += $response_lesson->inscrit;
						} else {
							$lessons[ $lesson_datetime_start->format( 'Y-m-d H:i:s' ) ] = ['starting' => $response_lesson->inscrit];
						}
						if ( isset( $lessons[ $lesson_datetime_end->format( 'Y-m-d H:i:s' ) ] ) ) {
							$lessons[ $lesson_datetime_end->format( 'Y-m-d H:i:s' ) ]['ending'] += $response_lesson->inscrit;
						} else {
							$lessons[ $lesson_datetime_end->format( 'Y-m-d H:i:s' ) ] = ['ending' => $response_lesson->inscrit];
						}

						// Assigning lesson to each period for registered & arrived
						foreach ( $period as $period_item ) {
							$period_item->setTimezone( wp_timezone() );

							if ( isset( $lessons[ $period_item->format( 'Y-m-d H:i:s' ) ]['registered'] ) ) {
								$lessons[ $period_item->format( 'Y-m-d H:i:s' ) ]['registered'] += $response_lesson->inscrit;
								$lessons[ $period_item->format( 'Y-m-d H:i:s' ) ]['arrived']    += $response_lesson->arrives;
							} else {
								$lessons[ $period_item->format( 'Y-m-d H:i:s' ) ] = [
									'registered' => $response_lesson->inscrit,
									'arrived'    => $response_lesson->arrives,
								];
							}

						}
					}

				}

			}

			// Save data in option
			update_option( 'tmsm-aquatonic-course-booking-lessons-data', $lessons );

		}
	}

	/**
	 * Do we have lessons data?
	 *
	 * @return bool
	 */
	public function lessons_has_data(){
		return !empty(get_option('tmsm-aquatonic-course-booking-lessons-data'));
	}

	/**
	 * Return lessons data
	 *
	 * @return array
	 */
	public function lessons_get_data(){
		return get_option('tmsm-aquatonic-course-booking-lessons-data');
	}


	/**
	 * Get lessons registered number for the time
	 *
	 * @param DateTimeInterface $datetime
	 *
	 * @return int
	 */
	public function lessons_registered_forthetime( DateTimeInterface $datetime){

		$count = 0;

		if($this->lessons_has_data()){
			$lessons_data = $this->lessons_get_data();
			if(!empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )])){
				if( ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]) && ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['registered'])){
					$count = $lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['registered'];
				}
			}

		}

		return $count;

	}

	/**
	 * Get lessons starting number for the time
	 *
	 * @param DateTimeInterface $datetime
	 *
	 * @return int
	 */
	public function lessons_starting_forthetime( DateTimeInterface $datetime){

		$count = 0;

		if($this->lessons_has_data()){
			$lessons_data = $this->lessons_get_data();
			if(!empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )])){
				if( ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]) && ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['starting'])){
					$count = $lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['starting'];
				}
			}

		}

		return $count;

	}

	/**
	 * Get lessons ending number for the time
	 *
	 * @param DateTimeInterface $datetime
	 *
	 * @return int
	 */
	public function lessons_ending_forthetime( DateTimeInterface $datetime){

		$count = 0;

		if($this->lessons_has_data()){
			$lessons_data = $this->lessons_get_data();
			if(!empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )])){
				if( ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]) && ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['ending'])){
					$count = $lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['ending'];
				}
			}

		}

		return $count;

	}

	/**
	 * Get lessons arrived number for the time
	 *
	 * @param DateTimeInterface $datetime
	 *
	 * @return int
	 */
	public function lessons_arrived_forthetime( DateTimeInterface $datetime){

		$count = 0;

		if($this->lessons_has_data()){
			$lessons_data = $this->lessons_get_data();
			if(!empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )])){
				if( ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]) && ! empty($lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['arrived'])){
					$count = $lessons_data[$datetime->format( 'Y-m-d H:i:s' )]['arrived'];
				}
			}

		}

		return $count;

	}

	/**
	 * Customize admin capabilities
	 *
	 * @param $caps
	 * @param $cap
	 * @param $user_id
	 * @param $args
	 *
	 * @return array|mixed
	 */
	function map_meta_cap($caps, $cap, $user_id, $args)
	{
		if (!is_user_logged_in()) return $caps;

		//print_r($caps);
		$target_roles = array('editor', 'administrator', 'shop_manager');
		$user_meta = get_userdata($user_id);
		$user_roles = ( array ) $user_meta->roles;

		if ( array_intersect($target_roles, $user_roles) ) {
			if ('manage_options_course' === $cap) {
				$manage_name = is_multisite() ? 'manage_network' : 'manage_options';
				$caps = array_diff($caps, [ $manage_name ]);
			}
			if ('manage_privacy_options' === $cap) {
				$manage_name = is_multisite() ? 'manage_network' : 'manage_options';
				$caps = array_diff($caps, [ $manage_name ]);
			}
		}
		return $caps;
	}


	/**
	 * Option Page capabilities
	 *
	 * @return string|null
	 */
	function option_page_capability() {
		$capability = null;
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$capability = 'manage_woocommerce';
		}

		return $capability;
	}


}
