<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Course_Booking_Activator {

	/**
	 * Activates the plugin
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		self::create_database_schema();
		self::create_cron_event();
		self::add_caps();

	}

	/**
	 * Add capabilities
	 *
	 * @since    1.0.0
	 */
	public static function add_caps() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$role = get_role( 'shop_manager' );
		$role->add_cap( 'aquatonic_course' );

	}
	/**
	 * Creates the database schema
	 *
	 * @link https://mac-blog.org.ua/wordpress-custom-database-table-example-full/
	 * @link https://premium.wpmudev.org/blog/creating-database-tables-for-plugins/
	 *
	 * @since    1.0.0
	 */
	public static function create_database_schema() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Bookings table
		$table_name = $wpdb->prefix . 'aquatonic_course_booking';
		$sql = "CREATE TABLE " . $table_name . " (
        booking_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        token VARCHAR(50) NOT NULL DEFAULT '',
        barcode VARCHAR(25) NOT NULL DEFAULT '',
        title TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        postalcode VARCHAR(20) NOT NULL,
        city VARCHAR(40) NOT NULL,
        birthdate DATE DEFAULT NULL,
        participants TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
        course_start DATETIME NULL DEFAULT NULL,
        course_end DATETIME NULL DEFAULT NULL,
        date_created DATETIME NULL DEFAULT NULL,
        author BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
        status VARCHAR(10) NOT NULL DEFAULT 'active',
        PRIMARY KEY (booking_id)
        ) $charset_collate;";
		dbDelta($sql);

		// History table
		$table_name = $wpdb->prefix . 'aquatonic_course_history';
		$sql = "CREATE TABLE " . $table_name . " (
        history_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        datetime DATETIME NULL DEFAULT NULL,
        canstart TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        courseallotment TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        realtime TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        ongoingbookings TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        PRIMARY KEY (history_id)
        ) $charset_collate;";
		dbDelta($sql);

		// save current database version for later use (on upgrade)
		update_option('tmsm-aquatonic-course-booking-db-version', TMSM_AQUATONIC_COURSE_BOOKING_DB_VERSION);

	}


	/**
	 * Creates the schedule event
	 *
	 * @since    1.0.0
	 */
	private static function create_cron_event() {

		if ( ! wp_next_scheduled( 'tmsm_aquatonic_course_refresh_event' ) ) {
			wp_schedule_event( time(), 'tmsm_aquatonic_course_refresh_schedule', 'tmsm_aquatonic_course_refresh_event' );
		}
	}

}
