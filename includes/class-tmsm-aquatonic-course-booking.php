<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Course_Booking {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tmsm_Aquatonic_Course_Booking_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_VERSION' ) ) {
			$this->version = TMSM_AQUATONIC_COURSE_BOOKING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tmsm-aquatonic-course-booking';

		$this->load_dependencies();
		$this->set_locale();
		$this->create_cron_schedule();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tmsm_Aquatonic_Course_Booking_Loader. Orchestrates the hooks of the plugin.
	 * - Tmsm_Aquatonic_Course_Booking_i18n. Defines internationalization functionality.
	 * - Tmsm_Aquatonic_Course_Booking_Admin. Defines all hooks for the admin area.
	 * - Tmsm_Aquatonic_Course_Booking_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// Include Pluggable to be able to use wp_get_current_user() function
		include_once(ABSPATH . 'wp-includes/pluggable.php');

		// Autoloading via composer
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '/vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-course-booking-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-course-booking-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tmsm-aquatonic-course-booking-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tmsm-aquatonic-course-booking-public.php';

		/**
		 * The class responsible for sanitizing user input
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-course-booking-sanitize.php';

		/**
		 * The class responsible for list table
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-course-booking-list-table.php';

		/**
		 * The classes for Dialog Insight (booking & contact)
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dialog-insight-api.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dialog-insight-booking.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dialog-insight-contact.php';

		$this->loader = new Tmsm_Aquatonic_Course_Booking_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tmsm_Aquatonic_Course_Booking_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tmsm_Aquatonic_Course_Booking_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tmsm_Aquatonic_Course_Booking_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Settings
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_sections' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_fields' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_fields' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'options_page_menu' );
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_'.$plugin_basename, $plugin_admin, 'settings_link' );
		//$this->loader->add_action( 'admin_head', $plugin_admin, 'dashboard_refresh' ); // not needed anymore, replaced by countdown

		// Mark as noshow bookings automatically every 5 minutes
		$this->loader->add_action( 'tmsm_aquatonic_course_refresh_event', $plugin_admin, 'bookings_mark_as_noshow', 10 );

		// Get lessons data every 5 minutes
		$this->loader->add_action( 'tmsm_aquatonic_course_refresh_event', $plugin_admin, 'lessons_set_data', 10 );

		// Send Aquos Contacts every 5 minutes
		$this->loader->add_action( 'tmsm_aquatonic_course_refresh_event', $plugin_admin, 'aquos_send_contacts_cron', 10 );

		// Calculate dashboard data
		$this->loader->add_action( 'tmsm_aquatonic_course_refresh_event', $plugin_admin, 'dashboard_calculate_data', 50 );

		// Ajax for change booking status
		$this->loader->add_action( 'wp_ajax_tmsm_aquatonic_course_booking_change_status', $plugin_admin, 'booking_change_status', 10 );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm_aquatonic_course_booking_change_status', $plugin_admin, 'booking_change_status', 10);


		// Capabilities
		//$this->loader->add_action( 'map_meta_cap', $plugin_admin, 'map_meta_cap', 10, 4 );
		//$this->loader->add_action('option_page_capability_aquatonic-course', $plugin_admin, 'option_page_capability');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tmsm_Aquatonic_Course_Booking_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_footer', $plugin_public, 'template_weekday_select' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'template_time_select' );


		// Gravity Forms Hooks only for the selected form
		$options = get_option($this->plugin_name . '-options');
		if(!empty($options)){
			$form_add_id = $options['gform_add_id'];
			$form_cancel_id = $options['gform_cancel_id'];
			if(!empty($form_add_id)){
				//$this->loader->add_filter( 'gform_entry_post_save_'.$form_add_id, $plugin_public, 'gform_entry_post_save_booking', 20, 2 );
				//$this->loader->add_action( 'gform_after_submission_'.$form_add_id, $plugin_public, 'gform_after_submission_booking', 20, 2 );
				$this->loader->add_action( 'gform_entry_created', $plugin_public, 'gform_entry_created', 20, 2 );
				$this->loader->add_action( 'gform_notification_'.$form_add_id, $plugin_public, 'gform_notification_booking', 20, 3 );
			}
			if(!empty($form_cancel_id)){
				$this->loader->add_filter( 'gform_pre_render_'.$form_cancel_id, $plugin_public, 'gform_pre_render_cancel', 20, 1 );
				$this->loader->add_action( 'gform_after_submission_'.$form_cancel_id, $plugin_public, 'gform_after_submission_cancel', 20, 2 );
			}
		}

		$this->loader->add_filter( 'gform_replace_merge_tags', $plugin_public, 'gform_replace_merge_tags_booking', 20, 7 );

		// Ajax frontend
		$this->loader->add_action( 'wp_ajax_tmsm-aquatonic-course-booking-times', $plugin_public, 'ajax_times' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquatonic-course-booking-times', $plugin_public, 'ajax_times' );

		// Barcode
		$this->loader->add_action( 'wp_ajax_tmsm-aquatonic-course-booking-generate-barcode', $plugin_public, 'generate_barcode_image', 10 );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquatonic-course-booking-generate-barcode', $plugin_public, 'generate_barcode_image', 10 );

		// Ajax for mini dashboard
		$this->loader->add_action( 'wp_ajax_tmsm-aquatonic-course-booking-minidashboard', $plugin_public, 'minidashboard', 10 );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquatonic-course-booking-minidashboard', $plugin_public, 'minidashboard', 10);

		// Emails
		$this->loader->add_filter( 'gform_html_message_template_pre_send_email', $plugin_public, 'gform_html_message_template_pre_send_email', 20, 1 );
		$this->loader->add_filter( 'gform_pre_send_email', $plugin_public, 'gform_pre_send_email', 20, 4 );
		$this->loader->add_filter( 'woocommerce_email_styles', $plugin_public, 'woocommerce_email_styles', 20, 2 );

		// Misc
		$this->loader->add_filter( 'body_class', $plugin_public, 'body_class_pages',10, 2);


	}



	/**
	 * Creates cron schedule
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function create_cron_schedule() {
		add_filter('cron_schedules', function($schedules) {
			$schedules['tmsm_aquatonic_course_refresh_schedule'] = array(
				'interval' => MINUTE_IN_SECONDS * 5,
				'display'  => __( 'Every 5 minutes', 'tmsm-aquatonic-course-booking' ),
			);
			return $schedules;
		}, 99);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tmsm_Aquatonic_Course_Booking_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


}
