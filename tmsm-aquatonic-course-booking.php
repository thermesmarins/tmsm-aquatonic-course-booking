<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.github.com/thermesmarins/
 * @since             1.0.0
 * @package           Tmsm_Aquatonic_Course_Booking
 *
 * @wordpress-plugin
 * Plugin Name:       TMSM Aquatonic Course Booking
 * Plugin URI:        https://www.github.com/thermesmarins/tmsm-aquatonic-course-booking/
 * Description:       Aquatonic Booking
 * Version:           1.0.0
 * Author:            Nicolas Mollet
 * Author URI:        https://github.com/nicomollet
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tmsm-aquatonic-course-booking
 * Domain Path:       /languages
 * Github Plugin URI: https://www.github.com/thermesmarins/tmsm-aquatonic-course-booking/
 * Github Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TMSM_AQUATONIC_COURSE_BOOKING_VERSION', '1.0.0' );
define( 'TMSM_AQUATONIC_COURSE_BOOKING_DB_VERSION', '1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tmsm-aquatonic-course-booking-activator.php
 */
function activate_tmsm_aquatonic_course_booking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-course-booking-activator.php';
	Tmsm_Aquatonic_Course_Booking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tmsm-aquatonic-course-booking-deactivator.php
 */
function deactivate_tmsm_aquatonic_course_booking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-course-booking-deactivator.php';
	Tmsm_Aquatonic_Course_Booking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tmsm_aquatonic_course_booking' );
register_deactivation_hook( __FILE__, 'deactivate_tmsm_aquatonic_course_booking' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-course-booking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tmsm_aquatonic_course_booking() {

	$plugin = new Tmsm_Aquatonic_Course_Booking();
	$plugin->run();

}
run_tmsm_aquatonic_course_booking();