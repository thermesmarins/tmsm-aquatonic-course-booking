<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Core class used to implement displaying bookings in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see WP_List_Table
 */
class Tmsm_Aquatonic_Course_History_List_Table extends WP_List_Table {

	/**
	 * Number of data per page
	 *
	 * @var integer
	 */
	protected $per_page = 200;

	/**
	 * Status (All/Active/Inactive)
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * Page slug
	 *
	 * @var string
	 */
	public $page = 'tmsm-aquatonic-course-booking-settings';
	public $tab = 'history';

	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->set_items();

		$total_items = $this->get_total_bookings();
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
		) );
	}

	/**
	 * Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'datetime'   => esc_html__( 'Date+Time', 'tmsm-aquatonic-course-booking' ),
			'realtime' => esc_html__( 'Real Time', 'tmsm-aquatonic-course-booking' ),
			'canstart'   => esc_html__( 'Can Start', 'tmsm-aquatonic-course-booking' ),
			'courseallotment' => esc_html__( 'Course Allotment', 'tmsm-aquatonic-course-booking' ),
			'ongoingbookings'  => esc_html__( 'On Going Participants', 'tmsm-aquatonic-course-booking' ),
		);
		return $columns;
	}

	/**
	 * Get the name of the default primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the default primary column, in this case, 'name'.
	 */
	protected function get_default_primary_column_name() {
		return 'datetime';
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'datetime'     => array( 'datetime', true ),
		);
	}

	/**
	 * Get where query
	 *
	 * @return string
	 */
	protected function get_where_query() {
		global $wpdb;

		$search_datecourse     = isset( $_GET['search_datecourse'] ) ? sanitize_text_field( wp_unslash( $_GET['search_datecourse'] ) ) : ''; // input var ok, CSRF ok.

		$where = '1 ';
		$where_and = ' AND ( 1 ';

		if ( ! empty( $search_datecourse ) ) {
			$objdate = \Datetime::createFromFormat('d/m/Y', $search_datecourse);
			if(!empty($objdate)){
				$where_and .= sprintf( " AND DATE(datetime) = '%s'", $objdate->format('Y-m-d') );
			}
		}

		$where_and .= ') ';

		$where.= $where_and;
		if ( ! empty( $where ) ) {
			$where = ' WHERE ' . $where;
		}

		return $where;
	}

	/**
	 * Set items for the table
	 */
	protected function set_items() {
		global $wpdb;
		$paged   = isset( $_GET['paged'] ) ? max( 0, intval( $_GET['paged'] ) - 1 ) : 0; // input var ok, CSRF ok.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'datetime'; // input var ok, CSRF ok.
		$order   = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'asc'; // input var ok, CSRF ok.


		$sql  = "SELECT * FROM `{$wpdb->prefix}aquatonic_course_history`";
		$sql .= $this->get_where_query();
		$sql .= ' ORDER BY ';
		$sql .= array_key_exists( $orderby, $this->get_sortable_columns()) || strpos($orderby, 'CONCAT') !== false ? $orderby : 'datetime';
		$sql .= ' ';
		$sql .= in_array( $order, array( 'asc', 'desc' ), true ) ? $order : 'asc';
		$sql .= ' LIMIT %d OFFSET %d';

		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, $this->per_page, $paged ), ARRAY_A );
	}

	/**
	 * Get total number of bookings
	 *
	 * @return int
	 */
	protected function get_total_history() {
		global $wpdb;
		$sql_count = "SELECT COUNT(*) FROM `{$wpdb->prefix}aquatonic_course_history`" . $this->get_where_query();
		return $wpdb->get_var( $sql_count ) ?? 0;
	}



	/**
	 * Checkbox column
	 *
	 * @param  array $item Item.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%s" value="%s">',
			'tmsm-aquatonic-course-booking[]',
			$item['history_id']
		);
	}

	/**
	 * Admin Page URL
	 *
	 * @return string
	 */
	private function admin_page_url(){

		$screen = get_current_screen();
		if(strpos($screen->base, 'toplevel' ) === false){
			return 'options-general.php';
		}
		else{
			return 'admin.php';
		}
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array  $item        Data.
	 * @param  String $column_name Current column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'history_id':
			case 'canstart':
			case 'courseallotment':
			case 'realtime':
			case 'ongoingbookings':
				return $item[ $column_name ];
			case 'ongoingbookings':
				$difference = $item[ 'courseallotment' ] - $item[ $column_name ];
				$color = 'red';
				if($difference >= 7){
					$color = 'orange';
				}
				if($difference >= 15){
					$color = 'green';
				}
				if($item[ 'courseallotment' ] )
				return $item[ $column_name ];
			case 'datetime' :
				$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $item[ $column_name ], wp_timezone() );
				return wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option('date_format'), get_option('time_format') ) , $objdate->getTimestamp() );

		}
	}





	/**
	 * Extra controls / filters
	 *
	 * @param  string $which top/bottom.
	 */
	protected function extra_tablenav( $which ) {
		//if ( 'top' === $which ) {
		//	echo '<div class="alignleft actions">';
		//	$this->status_filter();
		//	echo wp_kses_post( $this->search_box( esc_html__( 'Filter', 'tmsm-aquatonic-course-booking' ), 'filter' ) );
		//	echo '</div>';
		//}
	}

	/**
	 * Displays the search box.
	 *
	 * @since 3.1.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		echo '<div class="alignleft actions tablenav top">';

		$s      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // input var ok, CSRF ok.
		$search_datecourse = isset( $_REQUEST['search_datecourse'] ) ? esc_attr( wp_unslash( $_REQUEST['search_datecourse'] ) ) : '';

		if ( empty( $s ) && ! $this->has_items() ) {
			//return;
		}

		//print_r('$search_datecourse:'.$search_datecourse);
		//print_r('$search_datecreated:'.$search_datecreated);
		if ( empty( $search_datecourse ) && empty( $search_datecreated ) && empty( $s ) ) {
			$objdate = new Datetime();
			$search_datecourse = $objdate->format('d/m/Y');
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>

			<input type="search" minlength="3" placeholder="<?php echo esc_attr__( 'Name', 'tmsm-aquatonic-course-booking' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />

			<input type="search" placeholder="<?php echo esc_attr__( 'Course Date', 'tmsm-aquatonic-course-booking' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="search_datecourse" value="<?php echo $search_datecourse; ?>" />

			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		</div>
		<?php
	}


	/**
	 * Gets a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return string[] Array of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		$mode = get_user_setting( 'posts_list_mode', 'list' );

		$mode_class = esc_attr( 'table-view-' . $mode );

		return array( 'widefat', 'striped', $mode_class, $this->_args['plural'] );
	}
}
