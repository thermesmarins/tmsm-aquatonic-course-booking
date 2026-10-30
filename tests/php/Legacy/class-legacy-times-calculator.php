<?php
/**
 * RÉFÉRENCE FIGÉE — NE PAS MODIFIER.
 *
 * Copie à l'identique du calcul de créneaux tel qu'il existait avant l'optimisation
 * des requêtes (branche master, plugin v2.1.9), lignes 62 à 512 de
 * public/class-tmsm-aquatonic-course-booking-public.php.
 * Elle sert uniquement de "golden master" pour prouver que le nouveau code renvoie
 * exactement les mêmes créneaux. À supprimer une fois l'optimisation validée en production.
 */
class Legacy_Times_Calculator {

	private $plugin_name;

	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}

	/**
	 * Get Times from Web Service
	 *
	 * @return array
	 * @throws Exception
	 * @since    1.0.0
	 */
	private function _get_times() {
		global $wpdb;

		$user = wp_get_current_user();

		$times        = [];
		$closed_dates = [];
		$date         = date( 'Y-m-d' );
		$participants = 0;

		if ( ! empty( $_REQUEST['date'] ) ) {
			$date = sanitize_text_field( $_REQUEST['date'] );
		}
		$date_with_dash = $date;

		if ( ! empty( $_REQUEST['participants'] ) ) {
			$participants = sanitize_text_field( $_REQUEST['participants'] );
		}

		if ( ! empty( $date ) && $participants > 0 ) {
			//error_log('$date:'.$date);
			//error_log('$participants:'.$participants);

			$averagecourse = $this->get_option( 'courseaverage' );

			$slotsize    = $this->get_option( 'slotsize' );
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


			$interval = DateInterval::createFromDateString( $slotminutes . ' minutes' );


			$capacity_forthedate_timeslots = self::allotment_timeslots_forthedate( $date_with_dash );

			// If no timeslot, assume the course is closed
			if ( array_sum( $capacity_forthedate_timeslots ) === 0 ) {
				array_push( $closed_dates, $date_with_dash );
			}

			//usort($slots_in_opening_hours, function($a, $b) {
			//	return strcmp($a['start'], $b['start']);
			//});
			ksort( $capacity_forthedate_timeslots );
			//error_log('$capacity_forthedate_timeslots');
			//error_log(print_r($capacity_forthedate_timeslots , true));


			// Get first and last slot of all the slots
			$first_slot = null;
			$last_slot  = null;
			if ( ! empty( array_key_first( $capacity_forthedate_timeslots ) ) ) {
				$errors[]   = __( 'No first slot available to calculate all the slots', 'tmsm-aquatonic-course-booking' );
				$first_slot = DateTime::createFromFormat( 'Y-m-d H:i:s', array_key_first( $capacity_forthedate_timeslots ) );

			}
			if ( ! empty( array_key_last( $capacity_forthedate_timeslots ) ) ) {
				$errors[]  = __( 'No end slot available to calculate all the slots', 'tmsm-aquatonic-course-booking' );
				$last_slot = DateTime::createFromFormat( 'Y-m-d H:i:s', array_key_last( $capacity_forthedate_timeslots ) );
			}


			//error_log('$first_slot');
			//error_log(print_r($first_slot , true));
			//error_log('$last_slot');
			//error_log(print_r($last_slot , true));


			// Get all slots between first and last, then get participants for each one
			$uses = [];
			if ( ! empty( $first_slot ) && ! empty( $last_slot ) ) {
				$period = new DatePeriod( $first_slot, $interval, $last_slot );
				foreach ( $period as $slot_begin ) {
					//error_log( 'slot: '.$slot_begin->format( "Y-m-d H:i:s" ) );

					$uses[ $slot_begin->format( "Y-m-d H:i:s" ) ] = self::get_participants_ongoing_forthetime( $slot_begin );

				}

			}


			//error_log('$uses');
			//error_log(print_r($uses, true));

			$all_slots       = $capacity_forthedate_timeslots;
			$slots_available = [];
			// Get all available slots and calculate the remaining availability with participants number during all the duration of the slot
			$index = 1;
			foreach ( $all_slots as $slot_begin => $capacity ) {
				$slot_begin_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $slot_begin, wp_timezone() );
				$slot_end_object   = clone $slot_begin_object;
				$slot_end_object->modify( '+' . $averagecourse . ' minutes' );

				$allow_begin          = new DateTime( 'now', wp_timezone() );
				$allow_begin_customer = new DateTime( 'now', wp_timezone() );
				$allow_begin_customer->modify( '+' . ( 60 * $this->get_option( 'hoursbefore' ) ) . ' minutes' );
				$allow_end_customer = new DateTime( 'now', wp_timezone() );
				$allow_end_customer->modify( '+' . ( 60 * $this->get_option( 'hoursafter' ) ) . ' minutes' );

				// Is a customer
				if ( $this->user_has_role( wp_get_current_user(), 'customer' ) || ! $user || is_wp_error( $user ) || ! $user->ID ) {
					if ( $slot_begin_object < $allow_begin_customer || $slot_begin_object > $allow_end_customer ) {
						continue;
					}
				} // Is not a customer allow timeslot before "hours before"
				else {
					if ( $slot_begin_object < $allow_begin ) {
						continue;
					}
				}


				$period = new DatePeriod( $slot_begin_object, $interval, $slot_end_object );

				//error_log('calculate capacity for '.$slot_begin_object->format( "Y-m-d H:i:s" ));

				$min_capacity = 1000;
				foreach ( $period as $period_begin ) {
					$period_begin_uses           = $uses[ $period_begin->format( "Y-m-d H:i:s" ) ] ?? 0;
					$period_begin_total_capacity = $capacity_forthedate_timeslots[ $period_begin->format( "Y-m-d H:i:s" ) ] ?? 0;
					$min_capacity                = min( $min_capacity, $period_begin_total_capacity - $period_begin_uses );
				}
				if ( $participants <= $min_capacity ) {
					$slots_available[ $slot_begin ] = [
						'date'        => $slot_begin_object->format( 'Y-m-d' ),
						'hour'        => $slot_begin_object->format( 'H' ),
						'minutes'     => $slot_begin_object->format( 'i' ),
						'hourminutes' => $slot_begin_object->format( 'H:i' ),
						'capacity'    => $min_capacity,
						'index'       => $index,
					];
				}

				if ( isset( $slots_available[ $slot_begin ] ) && $slots_available[ $slot_begin ]['capacity'] <= 0 ) {
					unset( $slots_available[ $slot_begin ] );
				} else {
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

			$times = array_values( $slots_available );
		}

		if ( count( $times ) == 0 ) {
			$errors[] = __( 'No time slot found', 'tmsm-aquatonic-course-booking' );
			$times[]  = [
				'date'        => $date_with_dash,
				'hour'        => null,
				'minutes'     => null,
				'hourminutes' => null,
				'priority'    => null,
				'message'     => in_array( $date_with_dash, $closed_dates ) ? __( 'Closed', 'tmsm-aquatonic-course-booking' ) : __( 'No time slot found', 'tmsm-aquatonic-course-booking' ),
			];
		}

		return $times;
	}

	/**
	 * Get option
	 *
	 * @param string $option_name
	 *
	 * @return null
	 */
	private function get_option( $option_name = null ) {

		$options = get_option( $this->plugin_name . '-options' );

		if ( ! empty( $option_name ) ) {
			return $options[ $option_name ] ?? null;
		} else {
			return $options;
		}

	}

	/**
	 * Returns Opening Times for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array
	 */
	public function allotment_timeslots_forthedate( string $date = '' ) {

		$total_capacity = [];

		$opening_periods = self::allotment_periods_forthedate( $date );
		$date_with_dash  = $date;
		if ( empty( $opening_periods ) ) {
			return $total_capacity;
		}

		$averagecourse = $this->get_option( 'courseaverage' );

		$slotsize    = $this->get_option( 'slotsize' );
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


		$interval = DateInterval::createFromDateString( $slotminutes . ' minutes' );


		//error_log('$opening_periods');
		//error_log(print_r($opening_periods, true));

		//print_r('$opening_periods');
		//print_r($opening_periods);

		// First pass to calculate start and end datetimes
		foreach ( $opening_periods as &$opening_period ) {

			$opening_details  = explode( '-', $opening_period['times'] );
			$opening_capacity = explode( '-', $opening_period['capacity'] );
			$opening_start    = $opening_details[0];
			$opening_end      = $opening_details[1];

			$opening_period['start'] = DateTime::createFromFormat( 'Y-m-d H:i', $date_with_dash . ' ' . $opening_start );
			$opening_period['end']   = DateTime::createFromFormat( 'Y-m-d H:i', $date_with_dash . ' ' . $opening_end );

		}

		//print_r('$opening_periods after first pass');
		//print_r($opening_periods);

		//error_log('$opening_periods after first pass');
		//error_log(print_r($opening_periods, true));

		unset( $opening_period );

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
				$total_capacity[ $slot_begin->format( "Y-m-d H:i:s" ) ] = $opening_period['capacity'];
			}


		}

		return $total_capacity;
	}

	/**
	 * Returns Allotment Opening Times for the requested date
	 *
	 * @param string $date (Y-m-d)
	 *
	 * @return array|null
	 */
	public function allotment_periods_forthedate( string $date = '' ) {

		$attendance_timeslots = self::allotment_periods_settings();

		if ( empty( $attendance_timeslots ) ) {
			return null;
		}

		if ( empty( $date ) ) {
			return null;
		}

		$date_object = DateTime::createFromFormat( 'Y-m-d', $date );
		if ( empty( $date_object ) ) {
			return null;
		}
		$times           = [];
		$timeslots       = $attendance_timeslots . PHP_EOL;
		$timeslots_items = preg_split( '/\r\n|\r|\n/', esc_attr( $timeslots ) );
		$open            = false;
		$capacity        = 0;

		//print_r('$timeslots_items');
		//print_r($timeslots_items);

		//error_log('$timeslots_items');
		//error_log(print_r($timeslots_items , true));

		// First pass to list all slots
		foreach ( $timeslots_items as &$timeslots_item ) {

			$tmp_timeslots_item       = $timeslots_item;
			$tmp_timeslots_item_array = explode( '=', $tmp_timeslots_item );

			if ( is_array( $tmp_timeslots_item_array ) && count( $tmp_timeslots_item_array ) === 3 ) {
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
		$date              = $date_object->format( 'Y-m-d' );

		// Second pass for slots matching date
		foreach ( $timeslots_items as $timeslots_key => $timeslots_item_to_parse ) {

			if ( isset( $timeslots_item_to_parse['date'] ) && $timeslots_item_to_parse['date'] == $date ) {
				$found_slots_for_date = true;
				foreach ( explode( ',', $timeslots_item_to_parse['times'] ) as $timeslots_times ) {
					$times[] = [ 'times' => $timeslots_times, 'capacity' => $timeslots_item_to_parse['capacity'] ];
				}
			}
		}
		//print_r('$times after second pass');
		//print_r( $times );

		if ( empty( $times ) ) {
			// Third pass for slots matching day of the week
			foreach ( $timeslots_items as $timeslots_key => $timeslots_item_to_parse ) {

				if ( isset( $timeslots_item_to_parse['daynumber'] ) && $timeslots_item_to_parse['daynumber'] == $date_dayoftheweek ) {
					foreach ( explode( ',', $timeslots_item_to_parse['times'] ) as $timeslots_times ) {
						$times[] = [ 'times' => $timeslots_times, 'capacity' => $timeslots_item_to_parse['capacity'] ];
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
	 * Returns Allotment Timeslots
	 *
	 * @return mixed|null
	 */
	private function allotment_periods_settings() {

		return $this->get_option( 'timeslots' );

	}

	/**
	 * Get participants number with an ongoing course for the time
	 *
	 * @param DateTimeInterface $datetime
	 *
	 * @return int
	 */
	public function get_participants_ongoing_forthetime( DateTimeInterface $datetime ) {
		global $wpdb;

		$uses_count = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(participants) FROM
{$wpdb->prefix}aquatonic_course_booking WHERE ( status = %s OR status = %s ) AND course_start <= %s AND course_end > %s", 'active', 'arrived', $datetime->format( "Y-m-d H:i:s" ), $datetime->format( "Y-m-d H:i:s" ) ) );
		if ( empty( $uses_count ) ) {
			$uses_count = 0;
		}

		return $uses_count;

	}

	/**
	 * Checks if a user has a role.
	 *
	 * @param int|\WP_User $user The user.
	 * @param string $role The role.
	 *
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
