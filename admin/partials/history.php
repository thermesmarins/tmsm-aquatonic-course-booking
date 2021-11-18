<?php
$history = new Tmsm_Aquatonic_Course_History_List_Table();
$history->prepare_items();
?>
<div id="dashboard-widgets" class="metabox-holder columns-1">
	<div id="postbox-container-4" class="postbox-container">
		<div class="meta-box-sortables ui-sortable">
			<div class="postbox" id="postbox-history">
				<div class="postbox-header"><h2 class="hndle ui-sortable-handle"><?php esc_html_e( 'Participants by date (arrived or active)','tmsm-aquatonic-course-booking' ); ?></h2></div>
				<div class="insidee">
					<div class="main">

						<form method="get" action="">
							<input type="hidden" name="page" value="<?php echo esc_attr( $history->page ); ?>"/>
							<input type="hidden" name="tab" value="<?php echo esc_attr( $history->tab ); ?>"/>
							<p class="search-box">
								<input type="search" placeholder="<?php echo esc_attr__( 'Course Date (MM/DD/YYYY)', 'tmsm-aquatonic-course-booking' ); ?>" name="search_datecourse" value="<?php
								$search_datecourse = isset( $_REQUEST['search_datecourse'] ) ? esc_attr( wp_unslash( $_REQUEST['search_datecourse'] ) ) : '';
								echo $search_datecourse; ?>" />
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
									<th scope="col"><?php esc_html_e( 'On going Bookings','tmsm-aquatonic-course-booking' ); ?></th>

								</tr>
								</thead>
								<tbody>

								<?php
								foreach($history->items as $history_item){
									echo '<tr>';
									;
									foreach($history->get_columns() as $column_key => $column_name ){

										$difference = $history_item[ 'courseallotment' ] - $history_item[ 'ongoingbookings' ];
										$color = 'danger';
										if($difference >= 7){
											$color = 'warning';
										}
										if($difference >= 15){
											$color = 'cool';
										}

										if( $column_key === 'ongoingbookings'){
											echo '<td class="'.$column_key.' '.$column_key.'-'.$color.'">';
										}
										else{
											echo '<td class="'.$column_key.'">';
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