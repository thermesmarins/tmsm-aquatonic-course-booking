<?php
$history = new Tmsm_Aquatonic_Course_History_List_Table();
$history->prepare_items();
?>
<div id="dashboard-widgets" class="metabox-holder columns-1">
	<div id="postbox-container-4" class="postbox-container">
		<div class="meta-box-sortables ui-sortable">
			<div class="postbox" id="postbox-history">
				<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Allotment History by Slot','tmsm-aquatonic-course-booking' ); ?></h2></div>
				<div class="insidee">
					<div class="main">

						<form method="get" action="">
							<input type="hidden" name="page" value="<?php echo esc_attr( $history->page ); ?>"/>
							<input type="hidden" name="tab" value="<?php echo esc_attr( $history->tab ); ?>"/>
							<p class="legend">
								<?php echo esc_html__( 'Legend:', 'tmsm-aquatonic-course-booking' ); ?>
								<span class="legend-upgrade"><?php echo esc_html__( 'Possible Allotment Upgrade', 'tmsm-aquatonic-course-booking' ); ?></span>
								<span class="legend-cool"><?php echo esc_html__( 'Allotment Poorly Filled', 'tmsm-aquatonic-course-booking' ); ?></span>
								<span class="legend-warning"><?php echo esc_html__( 'Allotment Medium Filled', 'tmsm-aquatonic-course-booking' ); ?></span>
								<span class="legend-danger"><?php echo esc_html__( 'Allotment Very Filled', 'tmsm-aquatonic-course-booking' ); ?></span>
							</p>
							<p class="search-box">
								<input type="search" placeholder="<?php echo esc_attr__( 'From (MM/DD/YYYY)', 'tmsm-aquatonic-course-booking' ); ?>" name="search_datecourse_begin" value="<?php
								$search_datecourse_begin = isset( $_REQUEST['search_datecourse_begin'] ) ? esc_html( wp_unslash( $_REQUEST['search_datecourse_begin'] ) ) : '';
								echo $search_datecourse_begin; ?>" />
								<input type="search" placeholder="<?php echo esc_attr__( 'To (MM/DD/YYYY)', 'tmsm-aquatonic-course-booking' ); ?>" name="search_datecourse_end" value="<?php
								$search_datecourse_end = isset( $_REQUEST['search_datecourse_end'] ) ? esc_html( wp_unslash( $_REQUEST['search_datecourse_end'] ) ) : '';
								echo $search_datecourse_end; ?>" />
								<?php submit_button( __( 'Filter','tmsm-aquatonic-course-booking' ), '', '', false, array( 'id' => 'search-submit' ) ); ?>
							</p>
						</form>

						<?php

						?>
						<div class="table-responsive">
							<table class="wp-list-table widefat striped table-view-list tmsm-aquatonic-course-booking-history">
								<thead>
								<tr>
									<th scope="col" class="manage-column column-primary"><?php esc_html_e( 'Date','tmsm-aquatonic-course-booking' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Real Time','tmsm-aquatonic-course-booking' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Can Start','tmsm-aquatonic-course-booking' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Course Allotment','tmsm-aquatonic-course-booking' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Course participants','tmsm-aquatonic-course-booking' ); ?></th>

								</tr>
								</thead>
								<tbody>

								<?php
								foreach($history->items as $history_item){
									echo '<tr>';
									foreach($history->get_columns() as $column_key => $column_name ){

										$difference = $history_item[ 'courseallotment' ] - $history_item[ 'ongoingbookings' ];
										$color = 'danger';
										$upgrade = 'upgradeno';

										if ( $history_item['canstart'] > 10 ) {
											$upgrade = 'upgradeyes';
										}
										if ( $difference >= 5 ) {
											$color   = 'warning';
											$upgrade = 'upgradeno';
										}
										if ( $difference >= 15 ) {
											$color   = 'cool';
											$upgrade = 'upgradeno';
										}

										if ( $column_key === 'ongoingbookings' ) {
											echo '<td class="' . $column_key . ' ' . $column_key . '-' . $color . '">';
										} elseif ( $column_key === 'canstart' ) {
											echo '<td class="' . $column_key . ' ' . $column_key . '-' . $upgrade . '">';
										} else {
											echo '<td class="' . $column_key . '">';
										}

										echo $history->column_default($history_item, $column_key);
										echo '</td>';
									}


									echo '</tr>';
								}
								?>
								</tbody>


							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>