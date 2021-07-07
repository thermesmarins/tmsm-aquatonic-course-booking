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

		<a class="nav-tab <?php if ( $tab == 'stats' ) {
			echo 'nav-tab-active';
		} ?>" href="<?php echo self::admin_page_url(); ?>?page=tmsm-aquatonic-course-booking-settings&tab=stats">
			<?php _e( 'Stats', 'tmsm-aquatonic-course-booking' ); ?>
		</a>

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

	if ( $tab == 'stats') {

		$bookings = new Tmsm_Aquatonic_Course_Booking_List_Table();

		echo '
	    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	    <script type="text/javascript">
	      google.charts.load("current", {"packages":["corechart"]});
	      google.charts.setOnLoadCallback(drawChart);
	
	      function drawChart() {
	
	        var data = google.visualization.arrayToDataTable([
	          [\''.__('Status','tmsm-aquatonic-course-booking').'\', \''.__('Number of bookings','tmsm-aquatonic-course-booking').'\'],
	          [\''.__('Active','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_items_active().'],
	          [\''.__('Arrived','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_items_arrived().'],
	          [\''.__('No-Show','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_items_noshow().'],
	          [\''.__('Cancelled','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_items_cancelled().'],
	        ]);
	
	        var options = {
	        backgroundColor: \'transparent\',
	        is3D: true,
	          title: \''.__('Booking Status','tmsm-aquatonic-course-booking').'\'
	        };
	
	        var chart = new google.visualization.PieChart(document.getElementById(\'piechart\'));
	
	        chart.draw(data, options);
	      }
	    </script><div id="piechart" style="width: 900px; height: 500px;"></div>
    ';
	}

	if ( $tab == 'dashboard' ) {

		if( has_action('tmsm_aquatonic_attendance_cronaction')){
			$cronevent = wp_next_scheduled( 'tmsm_aquatonic_attendance_cronaction' );
			$cronevent += 1 * MINUTE_IN_SECONDS; // Add 30 seconds to let cron event execute

			// Crontevent next schedule is in the past, add 5 minutes
			if($cronevent < time()){
				$cronevent = $cronevent + 5 * MINUTE_IN_SECONDS;
			}
			if( ! empty($cronevent)){
				echo '<p class="pull-right">';
				$date = wp_date( get_option( 'time_format' ), $cronevent );
				echo sprintf(__( 'Next Refresh at %s in %s', 'tmsm-aquatonic-course-booking' ), $date, '<b class="refresh nowrap" id="refresh-counter" data-time="'.esc_attr($cronevent).'"></b>');

				if( !empty($_GET['force-refresh-attendance']) && $_GET['force-refresh-attendance'] == 1 ){
					do_action( 'tmsm_aquatonic_attendance_cronaction' );
				}
				?>

				<a id="refresh-attendance-link" class="button" href="<?php echo admin_url( self::admin_page_url().'?page='.$this->plugin_name.'-settings&force-refresh-attendance=1' );?>"><?php _e( 'Force refresh attendance', 'tmsm-aquatonic-course-booking' ); ?></a>

				<?php
				echo '</p>';
			}
		}

		$dashboard = get_option('tmsm-aquatonic-course-booking-dashboard', '');
		if( ! empty( $dashboard ) ) {

			echo '<table class="wp-list-table widefat striped table-dashboard">';
			$dashboard_row_count = 0;
			foreach ( $dashboard as $dashboard_row){
				$dashboard_row_count++;
				if($dashboard_row_count === 1){
					echo '<thead>';
				}
				if($dashboard_row_count === 2){
					echo '<tbody>';
				}
				echo '<tr>';
				foreach($dashboard_row as $dashboard_cell){

					echo '<td class="tooltip-row">';
					echo $dashboard_cell ?? ' ';
					echo '</td>';

				}
				echo '</tr>';
				if($dashboard_row_count === 1){
					echo '</thead>';
				}
				if($dashboard_row_count === 2){
					echo '</tbody>';
				}
			}
			echo '</table>';
		}
		else{
			echo '<div class="update-message notice inline notice-error notice-alt">' . __( 'Aquatonic Attendance is not available',
					'tmsm-aquatonic-course-booking' ) . '</div>';
		}

	}

	if ( $tab == 'bookings' ) {

		$bookings = new Tmsm_Aquatonic_Course_Booking_List_Table();
		$bookings->prepare_items();

		?>

		<form id="<?php echo esc_attr( $bookings->page ); ?>-filter" method="get" action="">
			<input type="hidden" name="page" value="<?php echo esc_attr( $bookings->page ); ?>"/>
			<input type="hidden" name="tab" value="<?php echo esc_attr( $bookings->tab ); ?>"/>
			<?php $bookings->search_box( __( 'Filter', 'tmsm-aquatonic-course-booking' ), 'bookings' ); ?>
		</form>

		<form id="tmsm-aquatonic-course-booking-settings-table" method="post" action="">
			<div class="table-responsive">
				<?php $bookings->display(); ?>
			</div>
		</form>



		<?php
	}
	?>
</div>
