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
class Tmsm_Aquatonic_Course_Booking_List_Table extends WP_List_Table {

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
	public $tab = 'bookings';

	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();
		$this->set_items();

		$total_items = $this->get_total_items();
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
			//'cb'      => '<input type="checkbox">',
			//'booking_id'      => esc_html__( 'ID', 'tmsm-aquatonic-course-booking' ),
			'date_created'   => esc_html__( 'Date Created', 'tmsm-aquatonic-course-booking' ),
			'firstname'   => esc_html__( 'Firstname', 'tmsm-aquatonic-course-booking' ),
			'lastname'   => esc_html__( 'Lastname', 'tmsm-aquatonic-course-booking' ),
			'email'   => esc_html__( 'Email', 'tmsm-aquatonic-course-booking' ),
			'participants' => esc_html__( 'Participants', 'tmsm-aquatonic-course-booking' ),
			'course_start' => esc_html__( 'Course Start', 'tmsm-aquatonic-course-booking' ),
			'status'  => esc_html__( 'Status', 'tmsm-aquatonic-course-booking' ),
			'action'  => esc_html__( 'Actions', 'tmsm-aquatonic-course-booking' ),
		);
		return $columns;
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			//'firstname' => array( 'firstname', false ),
			//'firstname' => array( 'firstname', false ),
			'course_start'    => array( 'course_start', true ),
			'booking_id'    => array( 'booking_id', false ),
		);
	}

	/**
	 * Get where query
	 *
	 * @return string
	 */
	protected function get_where_query() {
		global $wpdb;

		$s      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // input var ok, CSRF ok.
		$search_datecourse     = isset( $_GET['search_datecourse'] ) ? sanitize_text_field( wp_unslash( $_GET['search_datecourse'] ) ) : ''; // input var ok, CSRF ok.
		$search_datecreated     = isset( $_GET['search_datecreated'] ) ? sanitize_text_field( wp_unslash( $_GET['search_datecreated'] ) ) : ''; // input var ok, CSRF ok.
		$status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'all'; // input var ok, CSRF ok.

		$this->status = $status;

		$where = '';
		$where_and = ' AND ( 1 ';

		if ( ! empty( $s ) ) {
			$like   = '%' . $wpdb->esc_like( $s ) . '%';
			$where .= sprintf( "(firstname LIKE '%s' OR lastname LIKE '%s' OR email LIKE '%s')", $like, $like, $like );
		}
		else{
			$where.= ' 1 ';
		}

		if ( ! empty( $search_datecourse ) ) {
			$objdate = \Datetime::createFromFormat('d/m/Y', $search_datecourse);
			if(!empty($objdate)){
				$where_and .= sprintf( " AND DATE(course_start) = '%s'", $objdate->format('Y-m-d') );
			}
		}

		if ( ! empty( $search_datecreated ) ) {
			$objdate = \Datetime::createFromFormat('d/m/Y', $search_datecreated);
			if(!empty($objdate)){
				$where_and .= sprintf( " AND DATE(date_created) = '%s'", $objdate->format('Y-m-d') );
			}
		}


		if ( empty( $search_datecourse ) && empty( $search_datecreated ) &&  empty( $s ) ) {
			$objdate = new Datetime();
			if(!empty($objdate)){
				$where_and .= sprintf( " AND DATE(course_start) = '%s'", $objdate->format('Y-m-d') );
			}
		}
		$where_and .= ') ';

		$where.= $where_and;

		if ( 'all' !== $status ) {
			$where .= 'AND ' .sprintf( 'status = "%s"', $wpdb->esc_like($status) );
		}
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
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'course_start'; // input var ok, CSRF ok.
		$order   = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'asc'; // input var ok, CSRF ok.

		$sql  = "SELECT * FROM `{$wpdb->prefix}aquatonic_course_booking`";
		$sql .= $this->get_where_query();
		$sql .= ' ORDER BY ';
		$sql .= in_array( $orderby, $this->get_sortable_columns(), true ) ? $orderby : 'course_start';
		$sql .= ' ';
		$sql .= in_array( $order, array( 'asc', 'desc' ), true ) ? $order : 'asc';
		$sql .= ' LIMIT %d OFFSET %d';

		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, $this->per_page, $paged ), ARRAY_A );
	}

	/**
	 * Get total number of items
	 *
	 * @return int
	 */
	protected function get_total_items() {
		global $wpdb;
		$sql_count = "SELECT COUNT(*) FROM `{$wpdb->prefix}aquatonic_course_booking`" . $this->get_where_query();
		return $wpdb->get_var( $sql_count );
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
			$item['booking_id']
		);
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
			case 'booking_id':
			case 'firstname':
			case 'lastname':
			case 'email':
			case 'participants':
				return $item[ $column_name ];
			case 'status':
				$statuses = Tmsm_Aquatonic_Course_Booking_Admin::booking_statuses();
				return '<mark class="' . $statuses[ $item[ $column_name ] ]['iconclass'] . '"><span>' . $statuses[ $item[ $column_name ]]['name'] .' </span></mark>';
			case 'course_start':
				$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $item[ $column_name ], wp_timezone() );
				return wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option('date_format'), get_option('time_format') ) , $objdate->getTimestamp() );
			case 'date_created':
				$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $item[ $column_name ], wp_timezone() );
				return wp_date( sprintf( __( '%s at %s', 'tmsm-aquatonic-course-booking' ), get_option('date_format'), get_option('time_format') ) , $objdate->getTimestamp() );
		}
	}

	/**
	 * Return title column.
	 *
	 * @param  array $item Item data.
	 * @return string
	 */
	public function column_action( $item ) {
		$statuses = Tmsm_Aquatonic_Course_Booking_Admin::booking_statuses();
		$output= '';
		if(in_array($item['status'], ['active', 'noshow'])  ){
			$link = wp_nonce_url( admin_url( 'admin-ajax.php?action=tmsm_aquatonic_course_booking_change_status&status=arrived&booking_id='
			                                 . $item['booking_id'] ),
				'tmsm_aquatonic_course_booking_change_status', 'tmsm_aquatonic_course_booking_nonce' );
			$output .= '<a class="'.  $statuses['arrived']['actionclass'].'" href="'.$link .'" title="'. esc_attr($statuses['arrived']['markas']) .'">'.$statuses['arrived']['markas'].'</a> ';
		}
		if(in_array($item['status'], ['active'])  ){
			$link = wp_nonce_url( admin_url( 'admin-ajax.php?action=tmsm_aquatonic_course_booking_change_status&status=cancelled&booking_id='
			                                 . $item['booking_id'] ),
				'tmsm_aquatonic_course_booking_change_status', 'tmsm_aquatonic_course_booking_nonce' );
			$output .= '<a class="'.  $statuses['cancelled']['actionclass'].'" href="'.$link .'" title="'. esc_attr($statuses['cancelled']['markas']) .'">'.$statuses['cancelled']['markas'].'</a> ';
		}

		return $output;
	}

	/**
	 * Get a list of bulk actions
	 *
	 * @return array
	 */
	/*protected function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);

		return $actions;
	}*/

	/**
	 * Process bulk action
	 */
	/*protected function process_bulk_action() {
		global $wpdb;
		if ( 'delete' === $this->current_action() ) {
			$nonce = sanitize_key( $_REQUEST['_wpnonce'] );
			if ( wp_verify_nonce( $nonce, 'bulk-' . $this->screen->base ) ) {
				$ids = isset( $_REQUEST['tmsm-aquatonic-course-booking'] ) ? $_REQUEST['tmsm-aquatonic-course-booking'] : array();
				foreach ( $ids as $id ) {
					echo $id;
					echo '<br>';
					// $wpdb->delete( $wpdb->prefix . 'tmsm-aquatonic-course-booking', array( 'booking_id' => $id ) );
				}
				// wp_redirect( esc_url( add_query_arg( array() ) ) );
			}
		}
	}*/

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$link = admin_url( 'admin.php' );
		$link = add_query_arg( 'page', $this->page, $link );
		$link = add_query_arg( 'tab', $this->tab, $link );

		$views = array(
			'all'      => esc_html__( 'All', 'tmsm-aquatonic-course-booking' ),
			'active'   => esc_html__( 'Active', 'tmsm-aquatonic-course-booking' ),
			'cancelled' => esc_html__( 'Cancelled', 'tmsm-aquatonic-course-booking' ),
			'noshow' => esc_html__( 'No-show', 'tmsm-aquatonic-course-booking' ),
		);

		$status_links = array();
		foreach ( $views as $k => $v ) {
			$status_link = $link;
			$class       = ( $this->status === $k ) ? 'current' : '';
			if ( 'all' !== $k ) {
				$status_link = add_query_arg( 'status', $k, $link );
			}
			$status_links[ $k ] = '<a href="' . esc_url( $status_link ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $v ) . '</a>';
		}
		return $status_links;
	}

	/**
	 * Extra controls / filters
	 *
	 * @param  string $which top/bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			echo '<div class="alignleft actions">';
			$this->status_filter();
			echo wp_kses_post( $this->search_box( esc_html__( 'Filter', 'tmsm-aquatonic-course-booking' ), 'filter' ) );
			echo '</div>';
		}
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
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			//return;
		}



		$search_datecourse = isset( $_REQUEST['search_datecourse'] ) ? esc_attr( wp_unslash( $_REQUEST['search_datecourse'] ) ) : '';
		$search_datecreated = isset( $_REQUEST['search_datecreated'] ) ? esc_attr( wp_unslash( $_REQUEST['search_datecreated'] ) ) : '';

		//print_r('$search_datecourse:'.$search_datecourse);
		//print_r('$search_datecreated:'.$search_datecreated);
		if ( empty( $search_datecourse ) && empty( $search_datecreated ) ) {
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

			<input type="search" placeholder="<?php echo esc_attr__( 'Name', 'tmsm-aquatonic-course-booking' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />

			<input type="search" placeholder="<?php echo esc_attr__( 'Course Date', 'tmsm-aquatonic-course-booking' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="search_datecourse" value="<?php echo $search_datecourse; ?>" />

			<input type="search" placeholder="<?php echo esc_attr__( 'Creation Date', 'tmsm-aquatonic-course-booking' ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="search_datecreated" value="<?php echo $search_datecreated; ?>" />

			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Filter status
	 */
	protected function status_filter() {
		if ( ! $this->has_items() ) {
			return;
		}
		?>
		<select name="status">
			<option value="all"<?php selected( 'all', $this->status ); ?>><?php echo esc_html__( 'Status', 'tmsm-aquatonic-course-booking' );?></option>
			<option value="active"<?php selected( 'active', $this->status ); ?>><?php echo esc_html__( 'Active', 'tmsm-aquatonic-course-booking' );?></option>
			<option value="cancelled"<?php selected( 'cancelled', $this->status ); ?>><?php echo esc_html__( 'Cancelled', 'tmsm-aquatonic-course-booking' );?></option>
			<option value="noshow"<?php selected( 'cancelled', $this->status ); ?>><?php echo esc_html__( 'No-Show', 'tmsm-aquatonic-course-booking' );?></option>
		</select>
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
