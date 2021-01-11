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

if($tab == 'bookings'){
	?>

	<table class="many-items-table wp-list-table widefat">
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
