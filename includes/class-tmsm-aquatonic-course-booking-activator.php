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
	 * Install custoom table
	 *
	 * @link https://mac-blog.org.ua/wordpress-custom-database-table-example-full/
	 * @link https://premium.wpmudev.org/blog/creating-database-tables-for-plugins/
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'aquatonic_course_booking'; // do not forget about tables prefix

		$charset_collate = $wpdb->get_charset_collate();

		// sql to create your table
		// NOTICE that:
		// 1. each field MUST be in separate line
		// 2. There must be two spaces between PRIMARY KEY and its name
		//    Like this: PRIMARY KEY[space][space](id)
		// otherwise dbDelta will not work
		$sql = "CREATE TABLE " . $table_name . " (
        booking_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        birthdate DATE NOT NULL,
        participants TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
        course_start DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        course_end DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        author BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
        PRIMARY KEY (booking_id)
        ) $charset_collate;";

		// we do not execute sql directly
		// we are calling dbDelta which cant migrate database
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		// save current database version for later use (on upgrade)
		add_option('tmsm_aquatonic_course_booking_db_version', TMSM_AQUATONIC_COURSE_BOOKING_DB_VERSION);

	}

}
