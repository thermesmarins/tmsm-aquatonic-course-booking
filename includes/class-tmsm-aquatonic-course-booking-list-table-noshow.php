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
class Tmsm_Aquatonic_Course_Booking_List_Table_Noshow extends WP_List_Table {

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
	public $tab = 'stats';

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

		$this->set_pagination_args( array(
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
			'fullname'   => esc_html__( 'Full Name', 'tmsm-aquatonic-course-booking' ),
			'count'  => esc_html__( 'No-Show Count', 'tmsm-aquatonic-course-booking' ),
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
		);
	}

	/**
	 * Get where query
	 *
	 * @return string
	 */
	protected function get_where_query() {
		global $wpdb;

		$where = ' status = "noshow" ';
		if ( ! empty( $where ) ) {
			$where = ' WHERE ' . $where .  ' GROUP BY "email"';
		}

		return $where;
	}

	/**
	 * Set items for the table
	 */
	protected function set_items() {
		global $wpdb;

		$paged   = isset( $_GET['paged'] ) ? max( 0, intval( $_GET['paged'] ) - 1 ) : 0; // input var ok, CSRF ok.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'count'; // input var ok, CSRF ok.
		$order   = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'asc'; // input var ok, CSRF ok.

		$sql  = "SELECT *, COUNT(*) as count FROM `{$wpdb->prefix}aquatonic_course_booking`";
		$sql .= $this->get_where_query();
		$sql .= ' HAVING count > 3 ORDER BY count ';
		$sql .= ' LIMIT %d OFFSET %d';

		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, $this->per_page, $paged ), ARRAY_A );
	}

	protected function display_tablenav( $which ) {

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
			case 'count':
				return $item[ $column_name ];
			case 'fullname':
				return '<a href="'.admin_url( self::admin_page_url(). '?page='.$this->page.'&tab=bookings&status=all&s='. $item['email']).'"><abbr title="'.esc_attr($item[ 'email' ] . '&#013;' . $item[ 'phone' ] . '&#013;' . $item[ 'barcode' ]).'">' . $item[ 'firstname' ] . ' ' . $item[ 'lastname' ] .  '</abbr></a>';
			case 'status':
				$statuses = Tmsm_Aquatonic_Course_Booking_Admin::booking_statuses();
				return '<mark class="' . $statuses[ $item[ $column_name ] ]['iconclass'] . '"><span>' . $statuses[ $item[ $column_name ]]['name'] .' </span></mark>';
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
