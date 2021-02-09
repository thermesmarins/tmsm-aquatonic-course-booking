<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Course_Booking_Deactivator {

	/**
	 * Deactivates plugins
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook('tmsm_aquatonic_course_noshow_cronaction');
	}

}
