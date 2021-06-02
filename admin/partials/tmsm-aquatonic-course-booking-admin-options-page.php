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
<div class="wrap tmsm-aquatonic-course-booking-wrap">

	<?php
	$current_user_id = get_current_user_id();

	$target_roles = array('administrator');
	$user_meta = get_userdata($current_user_id);
	$user_roles = ( array ) $user_meta->roles;
	?>
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php if ( $tab == 'dashboard' ) {
			echo 'nav-tab-active';
		} ?>" href="<?php echo self::admin_page_url();?>?page=tmsm-aquatonic-course-booking-settings">
			<?php _e( 'Dashboard', 'tmsm-aquatonic-course-booking' ); ?>
		</a>
		<a class="nav-tab <?php if ( $tab == 'bookings' ) {
			echo 'nav-tab-active';
		} ?>" href="<?php echo self::admin_page_url(); ?>?page=tmsm-aquatonic-course-booking-settings&tab=bookings">
			<?php _e( 'Bookings', 'tmsm-aquatonic-course-booking' ); ?>
		</a>

		<?php
		if ( array_intersect($target_roles, $user_roles) ) {
		?>
			<a class="nav-tab <?php if ( $tab == 'settings' ) {
				echo 'nav-tab-active';
			} ?>" href="<?php echo self::admin_page_url();?>?page=tmsm-aquatonic-course-booking-settings&tab=settings">
				<?php _e( 'Settings', 'tmsm-aquatonic-course-booking' ); ?>
			</a>
		<?php } ?>
	</h2>

	<?php



	$options       = get_option( $this->plugin_name . '-options' );

	if ( $tab == 'settings' ) {


		?>
		<form method="post" action="options.php"><?php


			if( ! empty($options['dialoginsight_idkey']) && ! empty($options['dialoginsight_apikey']) && ! empty($options['dialoginsight_idproject']) && ! empty($options['dialoginsight_relationaltableid'] ) ){
				// Testing Dialog Insight API
				try {
					$project = \Dialog_Insight_API::request( [], 'projects', 'Get' );
				} catch (Exception $exception) {
					//echo 'Exception reçue : ',  $exception->getMessage(), "\n";
					add_settings_error( 'dialoginsight_api', 'dialoginsight_errors',
						sprintf(__( 'Dialog Insight API connection failed with error code: %s', 'tmsm-aquatonic-course-booking' ), $exception->getMessage()), 'error' );

				}
			}
			settings_errors('dialoginsight_api');

			// Testing the Gravity Forms Add form
			Tmsm_Aquatonic_Course_Booking_Admin::gform_check_add_form($options['gform_add_id']);

			// Testing the Gravity Forms Cancel form
			Tmsm_Aquatonic_Course_Booking_Admin::gform_check_cancel_form($options['gform_cancel_id']);

			// Display options
			settings_fields( $this->plugin_name . '-options' );
			do_settings_sections( $this->plugin_name );
			submit_button( __( 'Save options', 'tmsm-aquatonic-course-booking' ) );

			?></form>
		<?php
	}

	if ( $tab == 'dashboard' ) {



		$now = new Datetime;
		$minidashboard = array();
		$canstart = 200; // Fake number of persons that can start, high on purpose
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

		// For Tests
		$now_for_testing_lessons = clone $now;
		$period_for_testing_lessons = clone $period;
		if( ! empty($options['tests_lessonsdate']) ){
			$now_for_testing_lessons_year = substr($options['tests_lessonsdate'], 0, 4);
			$now_for_testing_lessons_month = substr($options['tests_lessonsdate'], 4, 2);
			$now_for_testing_lessons_day = substr($options['tests_lessonsdate'], 6, 2);
			$now_for_testing_lessons->setDate($now_for_testing_lessons_year, $now_for_testing_lessons_month, $now_for_testing_lessons_day);
			$now_for_testing_lessons_plus_courseaverage = clone $now_for_testing_lessons;
			$now_for_testing_lessons_plus_courseaverage->modify( '+' . $averagecourse . ' minutes' );
			$period_for_testing_lessons = new DatePeriod( $now_for_testing_lessons, $interval, $now_for_testing_lessons_plus_courseaverage );
		}

		$plugin_public                 = new Tmsm_Aquatonic_Course_Booking_Public( $this->plugin_name, null );
		$plugin_admin                 = new Tmsm_Aquatonic_Course_Booking_Admin( $this->plugin_name, null );
		//echo '<pre>';
		$capacity_timeslots_forthedate = $plugin_public->capacity_timeslots_forthedate( date( 'Y-m-d' ) );
		$allotment_timeslots_forthedate = $plugin_public->allotment_timeslots_forthedate( date( 'Y-m-d' ) );
		$treatments_timeslots_forthedate = $plugin_public->treatments_capacity_timeslots_forthedate( date( 'Y-m-d' ) );

		//print_r( allotment_timeslots_forthedate );
		//echo '</pre>';

		$realtime = get_option( 'tmsm-aquatonic-attendance-count' );
		$realtime = max($realtime,0);
		if ( ! empty( $options['tests_realtimeattendance'] ) ) {
			$realtime = $options['tests_realtimeattendance'];
		}

		$lessons_data = get_transient('tmsm-aquatonic-course-booking-lessons-data');

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
						$minidashboard[ $counter ][ 'date' ] = $date;

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

				<!-- Attendance Capacity -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Capacity', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter                                   = 0;
					$capacity_timeslots_forthedate_counter    = [];
					$capacity_timeslots_forthedate_difference = [];
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							if ( !isset( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
								$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
							}
							echo '<span class="capacity capacity-' . $counter .'">' . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>';
							$capacity_timeslots_forthedate_counter[ $counter] = $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];

							if($counter != 1 && $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] != $capacity_timeslots_forthedate_counter[ $counter - 1]){
								$difference = ( ( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $capacity_timeslots_forthedate_counter[ $counter - 1]) >= 0 ? '+' : '') . ( $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $capacity_timeslots_forthedate_counter[ $counter - 1]);

								echo ' (<span class="capacity-different capacity-different-' . $counter . '">'.$difference .'</span>)';
								$capacity_timeslots_forthedate_difference[ $counter] = $difference;

							}
							?></td>
						<?php
					}
					?>
				</tr>


				<!-- Lessons Subscribed -->
				<?php if( $plugin_admin->lessons_has_data()) {
				?>
					<tr>
						<th scope="col"><?php esc_html_e( 'Subscribed Lesson Members', 'tmsm-aquatonic-course-booking' ); ?></th>
						<?php
						$counter = 0;
						$lessons_subscribed_forthedate_counter = [];
						$period_for_lessons = ($period_for_testing_lessons !== $period ? $period_for_testing_lessons : $period);

						foreach ( $period_for_lessons as $period_item ) {
							$period_item->setTimezone( wp_timezone() );
							$counter ++;
							$lessons_subscribed_forthedate_counter[ $counter ] = $plugin_admin->lessons_subscribed_forthetime( $period_item );

							?>
							<td class="tooltip-row"><?php
								echo '<span class="tooltip-trigger lessons-subscribed lessons-subscribed-' . $counter .'">' .$lessons_subscribed_forthedate_counter[ $counter ] . '</span>';

								if($period_for_testing_lessons != $period){
									echo ' ';
									echo __('(Test Mode)','tmsm-aquatonic-course-booking');
								}
								?></td>
							<?php
						}
						?>
					</tr>

				<?php }?>

				<!-- Lessons Arrived -->
				<!--<?php if( $plugin_admin->lessons_has_data()) {
				?>
					<tr>
						<th scope="col"><?php esc_html_e( 'Arrived Lesson Members', 'tmsm-aquatonic-course-booking' ); ?></th>
						<?php
						$counter = 0;
						$period_for_lessons = ($period_for_testing_lessons !== $period ? $period_for_testing_lessons : $period);

						foreach ( $period_for_lessons as $period_item ) {
							$period_item->setTimezone( wp_timezone() );
							$counter ++;

							?>
							<td class="tooltip-row"><?php
								echo '<span class="tooltip-trigger lessons-arrived lessons-arrived-' . $counter .'">' .$plugin_admin->lessons_arrived_forthetime( $period_item ) . '</span>';
								if($period_for_testing_lessons !== $period){
									echo ' ';
									echo __('(Test Mode)','tmsm-aquatonic-course-booking');
								}

								?></td>
							<?php
						}
						?>
					</tr>

				<?php }?>-->

				<!-- Treatements Capacity -->
				<?php if(!empty($treatments_timeslots_forthedate)){?>
				<tr>
					<th scope="col"><?php esc_html_e( 'Treatment+Course Allotment', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter                                   = 0;
					$treatments_timeslots_forthedate_counter    = [];
					$treatments_timeslots_forthedate_difference = [];
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							if ( !isset( $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
								$treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
							}
							echo '<span class="treatment treatment-' . $counter .'">' . $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]  .'</span>';

							     $treatments_timeslots_forthedate_counter[ $counter] = $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];

							if($counter != 1 && $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] != $treatments_timeslots_forthedate_counter[ $counter - 1]){
								$difference = ( ( $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $treatments_timeslots_forthedate_counter[ $counter - 1]) >= 0 ? '+' : '') . ( $treatments_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $treatments_timeslots_forthedate_counter[ $counter - 1]);

								echo ' (<span class="treatment-different treatment-different-' . $counter . '">'.$difference .'</span>)';
								$treatments_timeslots_forthedate_difference[ $counter] = $difference;

							}
							?></td>
						<?php
					}
					?>
				</tr>
				<?php } ?>

				<!-- Booking Allotments -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Booking Allotments', 'tmsm-aquatonic-course-booking' ); ?></th>
					<?php
					$counter                                   = 0;
					$allotment_timeslots_forthedate_counter    = [];
					$allotment_timeslots_forthedate_difference = [];
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php
							if ( isset( $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] ) ) {
								echo '<span class="allotment allotment-' . $counter .'">' . $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>';
							} else {
								$allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] = 0;
							}
							$allotment_timeslots_forthedate_counter[ $counter] = $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ];

							if($counter != 1 && $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] != $allotment_timeslots_forthedate_counter[ $counter - 1]){
								$difference = ( ( $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $allotment_timeslots_forthedate_counter[ $counter - 1]) >= 0 ? '+' : '') . ( $allotment_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] - $allotment_timeslots_forthedate_counter[ $counter - 1]);

								echo ' (<span class="allotment-different allotment-different-' . $counter . '">'.$difference .'</span>)';
								$allotment_timeslots_forthedate_difference[ $counter] = $difference;

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
						<td class="tooltip-row"><?php
							echo '<span class="tooltip-trigger booking-ongoing booking-ongoing-' . $counter .'">' .$plugin_public->get_participants_ongoing_forthetime( $period_item ) . '</span>';

							$bookings_inside = '';
							foreach($bookings_of_the_day as $booking){
								if( in_array($booking->status, ['active', 'arrived']) && $booking->course_start <= $period_item->format('Y-m-d H:i:s') && $period_item->format('Y-m-d H:i:s') <= $booking->course_end ){
									$bookings_inside.= ''.$booking->firstname . ' '. $booking->lastname. ' x'.$booking->participants.'<br>';
								}
							}
							if(!empty($bookings_inside)){
								$bookings_inside = '<div class="tooltip-content">'.$bookings_inside.'</div';
							}
							echo $bookings_inside;

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
						<td class="tooltip-row"><?php
							echo '<span class="tooltip-trigger booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>';

							$bookings_inside = '';
							foreach($bookings_of_the_day as $booking){
								if( in_array($booking->status, ['active', 'arrived']) && $period_item->format('Y-m-d H:i:s') == $booking->course_end ){
									$bookings_inside.= ''.$booking->firstname . ' '. $booking->lastname. ' x'.$booking->participants.'<br>';
								}
							}
							if(!empty($bookings_inside)){
								$bookings_inside = '<div class="tooltip-content">'.$bookings_inside.'</div';
							}
							echo $bookings_inside;

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
						<td class="tooltip-row"><?php
							echo '<span class="tooltip-trigger booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>';

							$bookings_inside = '';
							foreach($bookings_of_the_day as $booking){
								if( in_array($booking->status, ['active', 'arrived']) && $booking->course_start == $period_item->format('Y-m-d H:i:s') ){
									$bookings_inside.= ''.$booking->firstname . ' '. $booking->lastname. ' x'.$booking->participants.'<br>';
								}
							}
							if(!empty($bookings_inside)){
								$bookings_inside = '<div class="tooltip-content">'.$bookings_inside.'</div';
							}
							echo $bookings_inside;
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
					$free    = [];
					foreach ( $period as $period_item ) {
						$period_item->setTimezone( wp_timezone() );
						$counter ++;

						?>
						<td><?php

							// First "Free" column
							if($counter === 1){
								$free[ $counter] = (
									$capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ]
									- $realtime
								    //+ $plugin_public->get_participants_ending_forthetime( $period_item )
								    //- $plugin_public->get_participants_starting_forthetime( $period_item )
								);
								echo '<span class="free free-'. $counter .'">'
								     . $free[ $counter]
								     . '</span>'
								;

								if ( array_intersect($target_roles, $user_roles) ) {
									echo ' ('
									     . '<span class="capacity capacity-' . $counter . '">' . $capacity_timeslots_forthedate[ $period_item->format( 'Y-m-d H:i:s' ) ] . '</span>'
									     . '-' . '<span class="realtime">' . $realtime . '</span>'
									     //. '+' . '<span class="booking-ending booking-ending-' . $counter .'">' . $plugin_public->get_participants_ending_forthetime( $period_item ) . '</span>'
									     //. '-' . '<span class="booking-starting booking-starting-' . $counter .'">' . $plugin_public->get_participants_starting_forthetime( $period_item ) . '</span>'
									     . ')';
								}

							}
							// Other "Free" columns
							else{

								$free[ $counter] = ( $free[ ( $counter - 1 ) ]
								                     + $plugin_public->get_participants_ending_forthetime( $period_item )
								                     - $plugin_public->get_participants_starting_forthetime( $period_item )
								                     + ( $capacity_timeslots_forthedate_difference[ $counter ] ?? 0)
								                     + ( $plugin_admin->lessons_has_data() ? $lessons_subscribed_forthedate_counter[ ( $counter - 1 ) ] : 0 )
								                     - ( $plugin_admin->lessons_has_data() ? $lessons_subscribed_forthedate_counter[ $counter ] : 0 )
													 + ( !empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter - 1 ] : 0 )
													 - ( !empty( $treatments_timeslots_forthedate ) ? $treatments_timeslots_forthedate_counter[ $counter ] : 0 )
								);
								echo '<span class="free free-' . $counter . '">'
								     . $free[ $counter] . '</span>';

								if ( array_intersect($target_roles, $user_roles) ) {
									echo ' ('
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
									     . ( $plugin_admin->lessons_has_data() ? '+' . '<span class="lessons-subscribed lessons-subscribed-'
									                                             . ( $counter - 1 ) . '">'
									                                             . $lessons_subscribed_forthedate_counter[ ( $counter - 1 ) ]
									                                             . '</span>' : '' )
									     . ( $plugin_admin->lessons_has_data() ? '-' . '<span class="lessons-subscribed lessons-subscribed-'
									                                             . $counter . '">'
									                                             . $lessons_subscribed_forthedate_counter[ $counter ] . '</span>'
											: '' )

									     . ( !empty( $treatments_timeslots_forthedate ) ? '+' . '<span class="treatment treatment-'
									                                                      . $counter . '">'.  $treatments_timeslots_forthedate_counter[ $counter - 1]. '</span>' : '' )
									     . ( !empty( $treatments_timeslots_forthedate ) ? '-' . '<span class="treatment treatment-'
									                                                      . $counter . '">'.  $treatments_timeslots_forthedate_counter[ $counter ]. '</span>' : '' )


									     . ')';
								}
							}

							$minidashboard[ $counter ][ 'free' ] = $free[ $counter];

							if( $free[ $counter] < $canstart){
								$canstart_counter = $counter;
							}

							$canstart = min($canstart, $free[ $counter]);
							$minidashboard[ 0 ][ 'date' ] = esc_html__( 'Can Start', 'tmsm-aquatonic-course-booking' );
							$minidashboard[ 0 ][ 'free' ] = $canstart;

							?></td>
						<?php
					}
					?>
				</tr>

				<!-- Can Start -->
				<tr>
					<th scope="col"><?php echo esc_html__( 'Can Start', 'tmsm-aquatonic-course-booking' ); ?></th>
					<td><span class="free free-<?php echo $canstart_counter;?>"><?php echo $canstart ;?></span> </td>
					<td colspan="5"><?php esc_html_e( '(Minimum Value of Free Row)', 'tmsm-aquatonic-course-booking' ); ?></td>
				</tr>

				<!-- Real Time -->
				<tr>
					<th scope="col"><?php esc_html_e( 'Real Time', 'tmsm-aquatonic-course-booking' );
					?></th>
					<td><?php echo '<span class="realtime">' . $realtime . '</span>';
						if(!empty($options['tests_realtimeattendance']) && $options['tests_realtimeattendance'] != 0) {
							echo ' ';
							echo __( '(Test Mode)', 'tmsm-aquatonic-course-booking' );
						}
					?></td>
					<td colspan="5">
								<?php
								if( has_action('tmsm_aquatonic_attendance_cronaction')){
									$cronevent = wp_next_scheduled( 'tmsm_aquatonic_attendance_cronaction' );
									$cronevent += 1 * MINUTE_IN_SECONDS; // Add 30 seconds to let cron event execute

									// Crontevent next schedule is in the past, add 5 minutes
									if($cronevent < time()){
										$cronevent = $cronevent + 5 * MINUTE_IN_SECONDS;
									}
									if( ! empty($cronevent)){
										$date = wp_date( get_option( 'time_format' ), $cronevent );
										echo sprintf(__( 'Next Refresh at %s in %s', 'tmsm-aquatonic-course-booking' ), $date, '<b class="refresh nowrap" id="refresh-counter" data-time="'.esc_attr($cronevent).'"></b>');

										if( !empty($_GET['force-refresh-attendance']) && $_GET['force-refresh-attendance'] == 1 ){
											do_action( 'tmsm_aquatonic_attendance_cronaction' );
										}
										?>
										<a id="refresh-attendance-link" class="button" href="<?php echo admin_url( self::admin_page_url().'?page='.$this->plugin_name.'-settings&force-refresh-attendance=1' );?>"><?php _e( 'Force refresh attendance', 'tmsm-aquatonic-course-booking' ); ?></a>

										<?php
									}
								}


								?>

					</td>
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


			update_option('tmsm-aquatonic-course-booking-minidashboard', $minidashboard);

		}



	}

	if ( $tab == 'bookings' ) {

		$bookings = new Tmsm_Aquatonic_Course_Booking_List_Table();
		$bookings->prepare_items();

		//$bookings->views();
		?>

		<form id="<?php echo esc_attr( $bookings->page ); ?>-filter" method="get" action="">
		<input type="hidden" name="page" value="<?php echo esc_attr( $bookings->page ); ?>"/>
		<input type="hidden" name="tab" value="<?php echo esc_attr( $bookings->tab ); ?>"/>
			<?php
			//$bookings->search_box( __( 'Filter', 'complianz-gdpr' ), 'cmplz-cookiesnapshot' );
			//$bookings->date_select();
			$bookings->display();
			?>
		</form>

		<?php
	}
	?>
</div>
