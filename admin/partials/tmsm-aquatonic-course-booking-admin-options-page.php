<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @param $tab string options page
 * @param $bookings array bookings
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/admin/partials
 */
?>
<div class="wrap">


<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>


<h2 class="nav-tab-wrapper">
	<a class="nav-tab <?php if ( $tab == 'dashboard' ) echo 'nav-tab-active'; ?>" href="options-general.php?page=tmsm-aquatonic-course-booking-settings">
		<?php _e('Dashboard', 'tmsm-aquatonic-course-booking'); ?>
	</a>
	<a class="nav-tab <?php if ( $tab == 'bookings' ) echo 'nav-tab-active'; ?>" href="options-general.php?page=tmsm-aquatonic-course-booking-settings&tab=bookings">
		<?php _e('Today\'s Bookings', 'tmsm-aquatonic-course-booking'); ?>
	</a>

	<a class="nav-tab <?php if ( $tab == 'settings' ) echo 'nav-tab-active'; ?>" href="options-general.php?page=tmsm-aquatonic-course-booking-settings&tab=settings">
		<?php _e('Settings', 'tmsm-aquatonic-course-booking'); ?>
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

if($tab == 'dashboard'){

	$now = new Datetime;

	$second = $now->format("s");
	if($second > 0){
		$now->add(new DateInterval("PT".(60-$second)."S"));
	}
	$minute = $now->format("i");
	$minute = $minute % 15;
	if($minute != 0)
	{
		// Count difference
		$diff = 15 - $minute;
		// Add difference
		$now->add(new DateInterval("PT".$diff."M"));
		$now->modify('-15 minutes');
	}

	$options = get_option($this->plugin_name . '-options');
	$averagecourse = $options['courseaverage'];

	$slotsize = $options['slotsize'];
	$slotminutes = 15;
	if( $slotsize == 6){
		$slotminutes = 10;
	}
	if( $slotsize == 3){
		$slotminutes = 20;
	}
	if( $slotsize == 2){
		$slotminutes = 30;
	}

	$interval = DateInterval::createFromDateString($slotminutes . ' minutes');
	$now_plus_courseaverage = clone $now;
	$now_plus_courseaverage->modify( '+' . $averagecourse . ' minutes' );
	$period = new DatePeriod( $now, $interval, $now_plus_courseaverage );

	$plugin_public = new Tmsm_Aquatonic_Course_Booking_Public( $this->plugin_name, null );
	$capacity_timeslots_forthedate = $plugin_public->capacity_timeslots_forthedate(date('Y-m-d'));
	print_r($capacity_timeslots_forthedate);

	$realtime = get_option('tmsm-aquatonic-attendance-count');
	if($realtime === false){
		echo '<div class="update-message notice inline notice-error notice-alt">'.__('Aquatonic Attendance is not available', 'tmsm-aquatonic-course-booking').'</div>';

	}
	else{
		?>
		<br>
		<table class="wp-list-table widefat striped">
			<thead>
			<tr>
				<th></th>
				<?php
				foreach ( $period as $period_item ) {

					$date = wp_date( get_option( 'time_format' ), $period_item->getTimestamp() );

					?>
					<th scope="col"><?php echo $date. ' - '.$period_item->getTimestamp(); ?></th>
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
				foreach ( $period as $period_item ) {

					?>
					<td><?php
						if(isset($capacity_timeslots_forthedate[$period_item->format('Y-m-d H:i:s')])){
							echo $capacity_timeslots_forthedate[$period_item->format('Y-m-d H:i:s')];
						}
						else{
							$capacity_timeslots_forthedate[$period_item->format('Y-m-d H:i:s')] = 0;
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
				foreach ( $period as $period_item ) {

					?>
					<td><?php
						echo $plugin_public->get_participants_ongoing_forthetime($period_item);
						?></td>
					<?php
				}
				?>
			</tr>

			<!-- Ending Bookings -->
			<tr>
				<th scope="col"><?php esc_html_e( 'Ending Bookings', 'tmsm-aquatonic-course-booking' ); ?></th>
				<?php
				foreach ( $period as $period_item ) {

					?>
					<td><?php
						echo $plugin_public->get_participants_ending_forthetime($period_item);
						?></td>
					<?php
				}
				?>
			</tr>

			<!-- Starting Bookings -->
			<tr>
				<th scope="col"><?php esc_html_e( 'Starting Bookings', 'tmsm-aquatonic-course-booking' ); ?></th>
				<?php
				foreach ( $period as $period_item ) {

					?>
					<td><?php
						echo $plugin_public->get_participants_starting_forthetime($period_item);
						?></td>
					<?php
				}
				?>
			</tr>

			<!-- Free -->
			<tr>
				<th scope="col"><?php esc_html_e( 'Free', 'tmsm-aquatonic-course-booking' ); ?></th>
				<?php
				foreach ( $period as $period_item ) {

					?>
					<td><?php

						echo $capacity_timeslots_forthedate[$period_item->format('Y-m-d H:i:s')] - $realtime + $plugin_public->get_participants_ending_forthetime($period_item) - $plugin_public->get_participants_starting_forthetime($period_item);
						echo ' ('.$capacity_timeslots_forthedate[$period_item->format('Y-m-d H:i:s')] . '-'.$realtime. '+'.$plugin_public->get_participants_ending_forthetime($period_item).'-'.$plugin_public->get_participants_starting_forthetime($period_item).')';

						?></td>
					<?php
				}
				?>
			</tr>

			<!-- Can Start -->
			<tr>
				<th scope="col"><?php esc_html_e( 'Can Start', 'tmsm-aquatonic-course-booking' ); ?></th>
				<td>TODO</td>
				<td>TODO</td>
				<td>TODO</td>
				<td>TODO</td>
				<td>TODO</td>
				<td>TODO</td>
			</tr>

			<!-- Real Time -->
			<tr>
				<th scope="col"><?php esc_html_e( 'Real Time', 'tmsm-aquatonic-course-booking' ); ?></th>
				<td><?php echo $realtime; ?></td>
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

if($tab == 'bookings'){
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
		</tr>
		</thead>
		<tbody>
		<?php foreach($bookings as $booking){?>
		<tr>
			<td><?php echo sanitize_text_field($booking->firstname);?></td>
			<td><?php echo sanitize_text_field($booking->lastname);?></td>
			<td><?php echo sanitize_text_field($booking->email);?></td>
			<td><?php echo sanitize_text_field($booking->participants);?></td>
			<td><?php
				$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $booking->course_start );
				echo sanitize_text_field(sprintf( __('%s at %s', 'tmsm-aquatonic-course-booking'), $objdate->format( 'Y-m-d' ), $objdate->format( 'H:i' )));?></td>
			<td><?php echo sanitize_text_field($booking->status);?></td>
		</tr>
		<?php } ?>
		</tbody>

		<!--<tfoot>
		<tr>
			<th><a class="button button-secondary "><span class="dashicon dashicon-plus"></span> <?php esc_html_e( 'New Booking' , 'tmsm-aquatonic-course-booking' ); ?></a></th>
		</tr>
		</tfoot>-->
	</table>
<?php
}
?>
</div>
