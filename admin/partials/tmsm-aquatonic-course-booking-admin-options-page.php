<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @param $tab      string options page
 * @param $bookings_of_the_day array bookings
 *
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/admin/partials
 */
?>
<div class="wrap">


	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>


	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php if ( $tab == 'dashboard' ) {
			echo 'nav-tab-active';
		} ?>" href="options-general.php?page=tmsm-aquatonic-course-booking-settings">
			<?php _e( 'Dashboard', 'tmsm-aquatonic-course-booking' ); ?>
		</a>
		<a class="nav-tab <?php if ( $tab == 'bookings' ) {
			echo 'nav-tab-active';
		} ?>" href="options-general.php?page=tmsm-aquatonic-course-booking-settings&tab=bookings">
			<?php _e( 'Today\'s Bookings', 'tmsm-aquatonic-course-booking' ); ?>
		</a>

		<a class="nav-tab <?php if ( $tab == 'settings' ) {
			echo 'nav-tab-active';
		} ?>" href="options-general.php?page=tmsm-aquatonic-course-booking-settings&tab=settings">
			<?php _e( 'Settings', 'tmsm-aquatonic-course-booking' ); ?>
		</a>
	</h2>

	<?php
	if ( $tab == 'settings' ) {
		?>
		<form method="post" action="options.php"><?php
			settings_fields( $this->plugin_name . '-options' );
			do_settings_sections( $this->plugin_name );
			submit_button( __( 'Save options', 'tmsm-aquatonic-course-booking' ) );

			?></form>
		<?php
	}

	if ( $tab == 'dashboard' ) {

		$now = new Datetime;
		$canstart = 200;
		$canstart_counter = null;

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

		$options       = get_option( $this->plugin_name . '-options' );
		$averagecourse = $options['courseaverage'];

		$slotsize    = $options['slotsize'];
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

		$interval               = DateInterval::createFromDateString( $slotminutes . ' minutes' );
		$now_plus_courseaverage = clone $now;
		$now_plus_courseaverage->modify( '+' . $averagecourse . ' minutes' );
		$period = new DatePeriod( $now, $interval, $now_plus_courseaverage );

		$plugin_public                 = new Tmsm_Aquatonic_Course_Booking_Public( $this->plugin_name, null );
		//echo '<pre>';
		$capacity_timeslots_forthedate = $plugin_public->capacity_timeslots_forthedate( date( 'Y-m-d' ) );
		//print_r( $capacity_timeslots_forthedate );
		//echo '</pre>';

		$realtime = get_option( 'tmsm-aquatonic-attendance-count' );
		$realtime = 50; // For tests only
		// Mysql Query to change date of bookings:
		// UPDATE aq_6_aquatonic_course_booking SET course_start = course_start + INTERVAL 1 DAY, course_end = course_end + INTERVAL 1 DAY
		// UPDATE aq_6_aquatonic_course_booking SET course_start = CONCAT(CURDATE(), ' ', TIME(course_start)), course_end = CONCAT(CURDATE(),' ', TIME(course_end))

		// Display table only if realtime data exists
		if ( $realtime === false ) {
			echo '<div class="update-message notice inline notice-error notice-alt">' . __( 'Aquatonic Attendance is not available',
					'tmsm-aquatonic-course-booking' ) . '</div>';
		} else {
			?>
			<br>
			<table class="wp-list-table widefat striped table-dashboard">
				<thead>
				<tr>
					<th></th>
					<?php
					$counter = 0;
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						$date = wp_date( get_option( 'time_format' ), $period_item->getTimestamp() );

						?>
						<th scope="col">
							<?php
							echo $date; ?>
						</th>
						<?php
					}
					?>
				</tr>
				</thead>
				<tbody>

				<!-- Capacity -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Capacity', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter = 0;
					$capacity_timeslots_forthedate_counter = [];
					$capacity_timeslots_forthedate_difference = [];
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							if ( isset( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
								echo '<span class="capacity capacity-' . $counter .'">' . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]. '</span>';
							} else {
								$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
							}
							$capacity_timeslots_forthedate_counter[$counter] = $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];

							if($counter != 1 && $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] != $capacity_timeslots_forthedate_counter[$counter - 1]){
								$difference = (($capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $capacity_timeslots_forthedate_counter[$counter - 1]) >= 0 ? '+' : '-'). ($capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $capacity_timeslots_forthedate_counter[$counter - 1]);

								echo ' (<span class="capacity-different capacity-different-' . $counter . '">'.$difference .'</span>)';
								$capacity_timeslots_forthedate_difference[$counter] = $difference;

							}
							?></td>
						<?php
					}
					?>
				</tr>

				<!-- Ongoing Bookings -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Ongoing Bookings', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter = 0;
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							echo '<span class="booking-ongoing booking-ongoing-' . $counter .'">' .$plugin_public->get_participants_ongoing_forthetime( $period_item ) . '</span>';
							?></td>
						<?php
					}
					?>
				</tr>

				<!-- Ending Bookings -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Ending Bookings', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter = 0;
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							echo '<span class="booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>';
							?></td>
						<?php
					}
					?>
				</tr>

				<!-- Starting Bookings -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Starting Bookings', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter = 0;
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							echo '<span class="booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>';
							?></td>
						<?php
					}
					?>
				</tr>

				<!-- Free -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Free', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter = 0;
					$capacity_timeslots_forthedate_free = [];
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php

							// First "Free" column
							if($counter === 1){
								$capacity_timeslots_forthedate_free[$counter] = ($capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $realtime
								                                                 + $plugin_public->get_participants_ending_forthetime( $period_item )
								                                                 - $plugin_public->get_participants_starting_forthetime( $period_item ) );
								echo '<span class="free free-'. $counter .'">'
								     . $capacity_timeslots_forthedate_free[$counter]
								     . '</span>'
								;

								echo ' (' . '<span class="capacity capacity-' . $counter . '">' . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>' . '-' . '<span class="realtime">'
								     . $realtime . '</span>' . '+' . '<span class="booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>' . '-'
								     . '<span class="booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>' . ')';
							}
							// Other "Free" columns
							else{

								$capacity_timeslots_forthedate_free[$counter] = ( $capacity_timeslots_forthedate_free[ ( $counter - 1 ) ]
								                                                  + $plugin_public->get_participants_ending_forthetime( $period_item )
								                                                  - $plugin_public->get_participants_starting_forthetime( $period_item )
								                                                  + $capacity_timeslots_forthedate_difference[ $counter ]
								);
								echo '<span class="free free-' . $counter . '">'
								     . $capacity_timeslots_forthedate_free[$counter] . '</span>';

								echo ' ('
								     . '<span class="free free-'. ( $counter - 1 ) .'">'
								     . $capacity_timeslots_forthedate_free[( $counter - 1 )]
								     . '</span>'
								     . '+'
								     . '<span class="booking-ending booking-ending-' . $counter . '">'
								     . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>' . '-'
								     . '<span class="booking-starting booking-starting-' . $counter . '">'
								     . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
								     . ($capacity_timeslots_forthedate_difference[ $counter ] ? '<span class="capacity-different capacity-different-' . $counter . '">'
								     . $capacity_timeslots_forthedate_difference[ $counter ] . '</span>' : '')
								     . ')';
							}

							if($capacity_timeslots_forthedate_free[$counter] < $canstart){
								$canstart_counter = $counter;
							}

							$canstart = min($canstart,  $capacity_timeslots_forthedate_free[$counter]);


							?></td>
						<?php
					}
					?>
				</tr>

				<!-- Can Start -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Can Start', 'tmsm-aquatonic-course-booking' ); ?></th>
					<td colspan="5"><span class="free free-<?php echo $canstart_counter;?>"><?php echo $canstart ;?></span> <?php esc_html_e( '(Minimum Value of Free Row)', 'tmsm-aquatonic-course-booking' ); ?></td>
				</tr>

				<!-- Real Time -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Real Time', 'tmsm-aquatonic-course-booking' ); ?></th>
					<td colspan="5"><?php echo '<span class="realtime">' . $realtime . '</span>'; ?></td>
				</tr>

				</tbody>

				<!--<tfoot>
		<tr>
			<th scope="col"><?php esc_html_e( 'Real Time', 'tmsm-aquatonic-course-booking' ); ?></th>
			<td>40</td>
		</tr>
		</tfoot>-->
			</table>
			<?php

		}
		?>

		<?php
	}

	if ( $tab == 'bookings' ) {
		?>
		<br>
		<table class="wp-list-table widefat striped">
			<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Firstname', 'tmsm-aquatonic-course-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Lastname', 'tmsm-aquatonic-course-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Email', 'tmsm-aquatonic-course-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Participants', 'tmsm-aquatonic-course-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Start', 'tmsm-aquatonic-course-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'tmsm-aquatonic-course-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'tmsm-aquatonic-course-booking' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $bookings_of_the_day as $booking ) { ?>
				<tr id="booking-<?php echo esc_attr($booking->booking_id)?>">
					<td><?php echo sanitize_text_field( $booking->firstname ); ?></td>
					<td><?php echo sanitize_text_field( $booking->lastname ); ?></td>
					<td><?php echo sanitize_text_field( $booking->email ); ?></td>
					<td><?php echo sanitize_text_field( $booking->participants ); ?></td>
					<td><?php
						$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking->course_start, wp_timezone() );
						echo wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option('date_format'), get_option('time_format') ) , $objdate->getTimestamp() );
						?></td>
					<td class="status"><?php echo sanitize_text_field( $booking->status ); ?></td>
					<td class="actions">
						<?php if(in_array($booking->status, ['active', 'noshow'])  ){
							$link = wp_nonce_url( admin_url( 'admin-ajax.php?action=tmsm_aquatonic_course_booking_change_status&status=arrived&booking_id='
							                                 . $booking->booking_id ),
								'tmsm_aquatonic_course_booking_change_status', 'tmsm_aquatonic_course_booking_nonce' );
							?>
						<a class="button wc-action-button wc-action-button-arrived" href="<?php echo $link; ?>" aria-label="En cours"><?php esc_html_e( 'Mark as arrived',
								'tmsm-aquatonic-course-booking' ); ?></a>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<!--<tfoot>
		<tr>
			<th><a class="button button-secondary "><span class="dashicon dashicon-plus"></span> <?php esc_html_e( 'New Booking',
				'tmsm-aquatonic-course-booking' ); ?></a></th>
		</tr>
		</tfoot>-->
		</table>
		<?php
	}
	?>
</div>
