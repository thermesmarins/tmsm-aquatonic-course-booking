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

	}


	/**
	 * Register the Settings page.
	 *
	 * @since    1.0.0
	 */
	public function options_page_menu() {
		add_options_page( __('Aquatonic Course', 'tmsm-aquatonic-course-booking'), __('Aquatonic Course', 'tmsm-aquatonic-course-booking'), 'manage_options', $this->plugin_name.'-settings', array($this, 'options_page_display'));

	}

	/**
	 * Plugin Settings Link on plugin page
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	function settings_link( $links ) {
		$setting_link = array(
			'<a href="' . admin_url( 'options-general.php?page='.$this->plugin_name.'-settings' ) . '">'.__('Settings', 'tmsm-aquatonic-course-booking').'</a>',
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

		$today = new  DateTime();
		$tomorrow = clone $today;
		$tomorrow->modify('+1 day');

		$bookings_of_the_day = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM
{$wpdb->prefix}aquatonic_course_booking WHERE course_start >= %s AND course_end < %s ORDER BY course_start", $today->format( "Y-m-d" ).' 00:00:00', $tomorrow->format( "Y-m-d" ).' 00:00:00' ) );

		include_once( 'partials/' . $this->plugin_name . '-admin-options-page.php' );
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
			esc_html__( 'Timeslots', 'tmsm-aquatonic-course-booking' ),
			array( $this, 'field_textarea' ),
			$this->plugin_name,
			$this->plugin_name . '-times',
			array(
				'id' => 'timeslots',
				'description' => esc_html__( 'Format: Day Number=09:00-14:00,15:30-17:30 serapated by a line break. Day Number is: 0 for Sunday, 1 for Monday, etc. Also for special dates: Date=09:00-21:00=0 where Date is in format YYYY-MM-DD.', 'tmsm-aquatonic-course-booking' ),
			)
		);

		// Select frontend form
		foreach (GFAPI::get_forms() as $form){
			$forms[] = ['value' => $form['id'], 'label' => $form['title']];
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
				$participants = false;
				$times = false;
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
					if($field['cssClass'] === 'tmsm-aquatonic-course-phone'){
						$phone = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-participants'){
						$participants = true;
					}
					if($field['cssClass'] === 'tmsm-aquatonic-course-times'){
						$times = true;
					}

				}

				if( ! $name ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The name field needs the `tmsm-aquatonic-course-name` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $name ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The name field needs the `tmsm-aquatonic-course-name` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $birthdate ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The birthdate field needs the `tmsm-aquatonic-course-name` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $phone ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The phone field needs the `tmsm-aquatonic-course-phone` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $participants ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The participants field needs the `tmsm-aquatonic-course-phone` class', 'tmsm-aquatonic-course-booking'));
				}

				if( ! $times ){
					$wp_error->add('tmsm-aquatonic-course-booking-gform-add', __('The times field needs the `tmsm-aquatonic-course-phone` class', 'tmsm-aquatonic-course-booking'));
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
					if($field['cssClass'] === 'tmsm-aquatonic-course-summary'){
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
		if ( empty( $type ) ) { return; }
		if ( empty( $data ) ) { return; }
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

		if ( empty( $input['courseaverage'] ) || empty( $input['hoursafter'] ) || empty( $input['timeslots'] ) ) {
			add_settings_error( 'courseaverage', 'timeslots_errors', __( 'Timeslots fields all need to be defined', 'tmsm-aquatonic-course-booking' ),
				'error' );
		}

		if ( empty( $input['dialoginsight_idkey'] ) || empty( $input['dialoginsight_apikey'] ) || empty( $input['dialoginsight_idproject'] )
		     || empty( $input['dialoginsight_relationaltableid'] ) ) {
			add_settings_error( 'dialoginsight_idkey', 'dialoginsight_errors',
				__( 'Dialog Insight fields all need to be defined', 'tmsm-aquatonic-course-booking' ), 'error' );
		}

		// TODO check if GF Add has all classes
		// TODO check if GF Cancel has all classes

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
		if ( ! empty( $this->options[$atts['id']] ) ) {
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
		$options[] = array( 'hoursbefore', 'text', '' );
		$options[] = array( 'hoursafter', 'text', '' );
		$options[] = array( 'timeslots', 'textarea', '' );

		$options[] = array( 'gform_add_id', 'text', '' );
		$options[] = array( 'gform_cancel_id', 'text', '' );

		$options[] = array( 'dialoginsight_idkey', 'text', '' );
		$options[] = array( 'dialoginsight_apikey', 'text', '' );
		$options[] = array( 'dialoginsight_idproject', 'text', '' );
		$options[] = array( 'dialoginsight_relationaltableid', 'text', '' );

		return $options;
	}

	/*
	 * Refresh every 5 minutes the dashboard page
	 */
	public function dashboard_refresh(){
		global $pagenow;
		$screen = get_current_screen();
		if( $pagenow === 'options-general.php' && $screen && $screen->id === 'settings_page_tmsm-aquatonic-course-booking-settings' && empty($_REQUEST['tab']) ){
			echo '<meta http-equiv="refresh" content="' . (MINUTE_IN_SECONDS * 5) . '; url=options-general.php?page=tmsm-aquatonic-course-booking-settings">';
		}
	}


	/**
	 * Mark Bookings as Now How
	 */
	public function bookings_mark_as_noshow(){
		global $wpdb;

		$nowminus15minutes = new  DateTime('now', wp_timezone());
		$nowminus15minutes->modify('-15 minutes');

		$mark_as_noshow_query = $wpdb->query( $wpdb->prepare( "UPDATE
{$wpdb->prefix}aquatonic_course_booking SET status='noshow' WHERE status = %s AND course_start < %s", 'active', $nowminus15minutes->format( "Y-m-d H:i:s" ) ) );




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

		$barcode = sanitize_text_field($_REQUEST['barcode']);
		$status = sanitize_text_field($_REQUEST['status']);

		$redirect_to_admin = true;

		if(! empty($barcode)){
			$redirect_to_admin = false;
		}

		if( $redirect_to_admin === false){

			$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE barcode = %s ", $barcode ), ARRAY_A );

			// Booking doesnt exist
			if( empty($booking)) {
				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Booking not found', 'tmsm-aquatonic-course-booking' ) ) );
			}

			$booking_id = $booking['booking_id'];

			// Booking is already arrived
			if($booking['status'] === 'arrived' && $status === 'arrived'){
				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Participant is already arrived', 'tmsm-aquatonic-course-booking' ) ) );
			}

			// Booking is cancelled
			if($booking['status'] === 'cancelled'){
				wp_send_json( array( 'success' => false, 'message' => esc_html__( 'Booking was cancelled', 'tmsm-aquatonic-course-booking' ) ) );
			}

			// Booking date passed or in the future
			$now = new Datetime();
			$booking_start_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking['course_start'], wp_timezone());

			if($booking_start_object->format('Y-m-d') !== $now->format('Y-m-d')){
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
			$booking_update = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}aquatonic_course_booking SET status = %s WHERE booking_id= %d ", $status, $booking_id ) );

			// Dialog Insight: Mark contact as customer if arrived
			if($status === 'arrived'){
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}aquatonic_course_booking WHERE booking_id= %d ", $booking_id ), ARRAY_A );

				// Update booking in Dialog Insight
				$booking_dialoginsight = new \Tmsm_Aquatonic_Course_Booking\Dialog_Insight_Booking();
				$booking_dialoginsight->token = $booking['token'];
				$booking_dialoginsight->status = 'arrived';
				try {
					$booking_dialoginsight->update();
				} catch (Exception $exception) {

					if($redirect_to_admin === false){
						wp_send_json( array( 'success' => false, 'message' => $exception->getMessage() ) );
					}

				}

			}

		}

		// Return JSON
		if($redirect_to_admin === false){
			wp_send_json(array('success' => true));
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
				'iconclass' => 'order-status status-on-hold status-active',
			],
			'arrived' => [
				'name' => __( 'Arrived', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Mark as arrived', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'order-status status-processing status-arrived',
				'actionclass' => 'button wc-action-button wc-action-button-processing processing',
			],
			'cancelled' => [
				'name' => __( 'Cancelled', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Mark as arrived', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'order-status status-failed status-cancelled',
			],
			'noshow' => [
				'name' => __( 'No-show', 'tmsm-aquatonic-course-booking' ),
				'markas' => __( 'Mark as no-show', 'tmsm-aquatonic-course-booking' ),
				'iconclass' => 'order-status status-pending status-noshow',
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
}
