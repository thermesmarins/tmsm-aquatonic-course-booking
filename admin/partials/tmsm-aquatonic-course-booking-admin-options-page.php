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
		?>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
      google.charts.load("current", {"packages":["corechart", "line"], "locale": "fr-FR", "language": "fr-FR"});


	</script>
	<div id="dashboard-widgets" class="metabox-holder columns-1">
		<div id="postbox-container-4" class="postbox-container">
			<div class="meta-box-sortables ui-sortable">
				<div class="postbox" id="postbox-stats-listfuturedates">
					<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Future Participants by date','tmsm-aquatonic-course-booking' ); ?></h2></div>
					<div class="insidee">
						<div class="main">

							<form method="get" action="">
								<input type="hidden" name="page" value="<?php echo esc_attr( $bookings->page ); ?>"/>
								<input type="hidden" name="tab" value="stats"/>
								<p class="search-box">
									<input type="search" placeholder="<?php echo esc_attr__( 'Course Date', 'tmsm-aquatonic-course-booking' ); ?>" name="search_datecourse" value="<?php
									$search_datecourse = isset( $_REQUEST['search_datecourse'] ) ? esc_attr( wp_unslash( $_REQUEST['search_datecourse'] ) ) : '';
									echo $search_datecourse; ?>" />
									<?php submit_button( __( 'Filter','tmsm-aquatonic-course-booking' ), '', '', false, array( 'id' => 'search-submit' ) ); ?>
								</p>
							</form>


							<?php
							$total_future_participants_by_coursestart_active = $bookings->get_total_future_participants_by_coursestart_active();

							?>
							<div class="table-responsive">
								<table class="wp-list-table widefat table-view-list settings_page_tmsm-aquatonic-course-booking-settings">
									<thead>
									<tr>
										<th scope="col" class="manage-column column-primary"><?php esc_html_e( 'Date','tmsm-aquatonic-course-booking' ); ?></th>

										<?php
										foreach($total_future_participants_by_coursestart_active as $date){
											$date_object = Datetime::createFromFormat( 'Y-m-d', $date->course_start);
											$date_formatted = wp_date( 'D j M', $date_object->getTimestamp() );

											echo '<td data-weekday="'.$date_object->format('N').'" '.(in_array($date_object->format('N') , [6, 7]) ? 'class="weekend alternate"' : '').'>'.$date_formatted.'</td>';
										}
										?>

									</tr>
									</thead>

									<tbody >
									<tr>
										<th scope="col" class="manage-column" >
											<?php esc_html_e( 'Participants','tmsm-aquatonic-course-booking' ); ?>
										</th>
										<?php
										foreach($total_future_participants_by_coursestart_active as $date){
											echo '<td>'.$date->participants.'</td>';
										}
										?>

									</tr>
									</tbody>

								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="dashboard-widgets" class="metabox-holder columns-1">
		<div id="postbox-container-3" class="postbox-container">
			<div class="meta-box-sortables ui-sortable">
				<div class="postbox">
					<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Past Bookings by date and by status','tmsm-aquatonic-course-booking' ); ?></h2></div>
					<div class="inside">
						<div class="main">
							<div id="chart_div"></div>
							<?php
							$bookings = new Tmsm_Aquatonic_Course_Booking_List_Table();
							$chart_rows = '';

							$bookings_total_items_by_coursestart_arrived = $bookings->get_total_past_bookings_by_coursestart_arrived();
							$bookings_total_items_by_coursestart_cancelled = $bookings->get_total_past_bookings_by_coursestart_cancelled();
							$bookings_total_items_by_coursestart_noshow = $bookings->get_total_past_bookings_by_coursestart_noshow();

							$bookings_array = [];
							foreach ( $bookings_total_items_by_coursestart_arrived as $item ) {
								$bookings_array[ $item->course_start ]['arrived'] = $item->count;
							}
							foreach ( $bookings_total_items_by_coursestart_cancelled as $item ) {
								$bookings_array[ $item->course_start ]['cancelled'] = $item->count;
							}
							foreach ( $bookings_total_items_by_coursestart_noshow as $item ) {
								$bookings_array[ $item->course_start ]['noshow'] = $item->count;
							}
							ksort($bookings_array);
							foreach ( $bookings_array as $date => $values ) {
								$date_array = explode('-', $date);
								$chart_rows .= '[new Date('.$date_array[0] . ', ' . intval( $date_array[1] - 1 ) .', '.intval($date_array[2]).'), '. ( intval($values['arrived']) ?? 0 ) .', '.(intval($values['cancelled']) ?? 0) .', '. (intval($values['noshow']) ?? 0 ) .'],';
							}
							//print_r($bookings_array);
							?>
							<script>

                              function drawCurveTypes() {
                                var data = new google.visualization.DataTable();
                                data.addColumn('date', '<?php echo esc_attr_e('Date', 'tmsm-aquatonic-course-booking');?>');
                                data.addColumn('number', '<?php echo esc_attr_e('Arrived', 'tmsm-aquatonic-course-booking');?>');
                                data.addColumn('number', '<?php echo esc_attr_e('Cancelled', 'tmsm-aquatonic-course-booking');?>');
                                data.addColumn('number', '<?php echo esc_attr_e('No-Show', 'tmsm-aquatonic-course-booking');?>');

                                data.addRows([
									<?php echo $chart_rows ; ?>
                                ]);

                                var options = {
                                  hAxis: {
                                    title: '<?php echo esc_attr_e('Date', 'tmsm-aquatonic-course-booking');?>'
                                  },
                                  vAxis: {
                                    title: '<?php echo esc_attr_e('Bookings', 'tmsm-aquatonic-course-booking');?>'
                                  },

                                };

                                var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                                chart.draw(data, options);
                              }
                              google.charts.setOnLoadCallback(drawCurveTypes);

							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="dashboard-widgets" class="metabox-holder">
		<div id="postbox-container-1" class="postbox-container">
			<div class="meta-box-sortables ui-sortable">
		<div class="postbox">
			<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Total Bookings by Status','tmsm-aquatonic-course-booking' ); ?></h2></div>
			<div class="inside">
				<div class="main">
					<?php


		echo '
			<script>
		google.charts.setOnLoadCallback(drawChart);
	      function drawChart() {
	
	        var data = google.visualization.arrayToDataTable([
	          [\''.__('Status','tmsm-aquatonic-course-booking').'\', \''.__('Number of bookings','tmsm-aquatonic-course-booking').'\'],
	          [\''.__('Arrived','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_bookings_arrived() . '],
	          [\''.__('Cancelled','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_bookings_cancelled() . '],
	          [\''.__('No-Show','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_bookings_noshow() . '],
	          [\''.__('Active','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_bookings_active() . '],
	        ]);
	
	        var options = {
	        backgroundColor: \'transparent\',
	        is3D: true,
	        };
	
	        var chart = new google.visualization.PieChart(document.getElementById(\'piechart_bookingsbystatus\'));
	
	        chart.draw(data, options);
	      }
	    </script><div id="piechart_bookingsbystatus" style="width: 500px; height: 250px;"></div>
    ';

		?>
				</div>
				</div>
				</div>
				</div>
				</div>
		<div id="postbox-container-2" class="postbox-container">
			<div class="meta-box-sortables ui-sortable">
				<div class="postbox">
					<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Total Participants by Status','tmsm-aquatonic-course-booking' ); ?></h2></div>
					<div class="inside">
						<div class="main">
							<?php

							echo '
	    <script type="text/javascript">
	      google.charts.load("current", {"packages":["corechart", "line"], "locale": "fr-FR", "language": "fr-FR"});
	      
	      google.charts.setOnLoadCallback(drawChart);
	
	      function drawChart() {
	
	        var data = google.visualization.arrayToDataTable([
	          [\''.__('Status','tmsm-aquatonic-course-booking').'\', \''.esc_attr__('Number of participants','tmsm-aquatonic-course-booking').'\'],
	          [\''.__('Arrived','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_participants_arrived() . '],
	          [\''.__('Cancelled','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_participants_cancelled() . '],
	          [\''.__('No-Show','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_participants_noshow() . '],
	          [\''.__('Active','tmsm-aquatonic-course-booking').'\',      '.$bookings->get_total_participants_active() . '],
	        ]);
	
	        var options = {
	        backgroundColor: \'transparent\',
	        is3D: true,
	        };
	
	        var chart = new google.visualization.PieChart(document.getElementById(\'piechart_participantsbystatus\'));
	
	        chart.draw(data, options);
	      }
	    </script><div id="piechart_participantsbystatus" style="width: 500px; height: 250px;"></div>
    ';

							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="postbox-container-3" class="postbox-container">
					<div class="meta-box-sortables ui-sortable">

						<div id="postbox-stats-listmostnoshow" class="postbox">
							<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Customers with most no-shows (more than 4)','tmsm-aquatonic-course-booking' ); ?></h2></div>
							<div class="insidee">
								<div class="main">

					<?php


					$bookings = new Tmsm_Aquatonic_Course_Booking_List_Table_Noshow();
					$bookings->prepare_items();

					?>

					<form id="<?php echo esc_attr( $bookings->page ); ?>-filter" method="get" action="">
						<input type="hidden" name="page" value="<?php echo esc_attr( $bookings->page ); ?>"/>
						<input type="hidden" name="tab" value="<?php echo esc_attr( $bookings->tab ); ?>"/>
					</form>

					<form id="tmsm-aquatonic-course-booking-settings-table" method="post" action="">
						<div class="table-responsive">
							<?php $bookings->display(); ?>
						</div>
					</form>
								</div></div></div>



				</div>
				</div>

				</div>




			</div>

	<?php
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
