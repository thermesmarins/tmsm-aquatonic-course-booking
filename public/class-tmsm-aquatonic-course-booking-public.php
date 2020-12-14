<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquatonic_Course_Booking
 * @subpackage Tmsm_Aquatonic_Course_Booking/public
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Course_Booking_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tmsm_Aquatonic_Course_Booking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tmsm_Aquatonic_Course_Booking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-course-booking-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tmsm_Aquatonic_Course_Booking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tmsm_Aquatonic_Course_Booking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		$posts = get_posts( array(
			'post_status' => 'draft,publish',
			'page' => 1,
		) );

		$post_data = array();
		foreach ( $posts as $post ) {
			$post_data[] = array(
				'id' => $post->ID,
				'title' => array(
					'rendered' => $post->post_title,
				),
				'status' => $post->post_status,
			);
		}

		wp_enqueue_script( 'jquery-mask', plugin_dir_url( __FILE__ ) . 'js/jquery.mask.min.js', array( 'jquery' ), null, true );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-course-booking-public.js', array( 'wp-backbone', 'moment', 'jquery', 'jquery-mask', 'gform_gravityforms' ), $this->version, true );

		// Javascript localization
		$translation_array = array(
			'birthdateformat' => _x( 'mm/dd/yyyy', 'birthdate date format for humans', 'tmsm-aquatonic-course-booking' ),
		);
		wp_localize_script( $this->plugin_name, 'tmsm_aquatonic_course_booking_i18n', $translation_array );


		// Rest data
		wp_localize_script(
			$this->plugin_name,
			'bbdata',
			array(
				'posts' => $post_data,
				'rest_url' => get_rest_url(),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

	}


	/**
	 * Get option
	 * @param string $option_name
	 *
	 * @return null
	 */
	private function get_option($option_name = null){

		$options = get_option($this->plugin_name . '-options');

		if(!empty($option_name)){
			return $options[$option_name] ?? null;
		}
		else{
			return $options;
		}

	}

	/**
	 * Have Voucher Template
	 */
	public function template_day(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquos-spa-booking-havevoucher">

			<p><strong></strong></p>
			<ul></ul>

		</script>
		<?php
	}

	/**
	 * Template
	 */
	public function template_post(){
		?>

		<script type="text/template" id="tmpl-bb-post">
			<td><input type="text" class="title" value="{{{ data.title }}}" /></td>
			<# //console.log(data) #>
			<td>
				<select class="status">
					<option value="publish"<# if ( data.status == 'publish' ) { #> SELECTED<# } #>><?php echo esc_html( __( 'Published', 'tmsm-aquatonic-course-booking' ) ) ?></option>
					<option value="draft"<# if ( data.status == 'draft' ) { #> SELECTED<# } #>><?php echo esc_html( __( 'Draft', 'tmsm-aquatonic-course-booking' ) ) ?></option>
				</select>
			</td>
			<td>
				<button class="button save"><?php echo esc_html( __( 'Save', 'tmsm-aquatonic-course-booking' ) ) ?></button>
			</td>
		</script>
		<?php
	}

	/**
	 * Template
	 */
	public function template_postlisting(){
		?>

		<script type="text/template" id="tmpl-bb-post-listing">
			<table class="wp-list-table widefat">
				<thead>
				<th><?php echo esc_html( __( 'Title', 'tmsm-aquatonic-course-booking' ) ) ?></th>
				<th><?php echo esc_html( __( 'Publish Status', 'tmsm-aquatonic-course-booking' ) ) ?></th>
				<th><?php echo esc_html( __( 'Action', 'tmsm-aquatonic-course-booking' ) ) ?></th>
				<th>{{ moment().add(0, 'days').format('MMMM Do YYYY, h:mm:ss a') }}</th>
				</thead>
				<tbody class="bb-posts"></tbody>
			</table>

			<p><button class="button button-primary refresh"><?php echo esc_html( __( 'Refresh', 'tmsm-aquatonic-course-booking' ) ) ?></button></p>
			<p><button class="btn-default previous"><?php echo esc_html( __( 'Previous', 'tmsm-aquatonic-course-booking' ) ) ?></button></p>
			<p><button class="btn-default next"><?php echo esc_html( __( 'Next', 'tmsm-aquatonic-course-booking' ) ) ?></button></p>
		</script>
		<?php
	}


	/**
	 * Booking Submission
	 *
	 * @param $entry
	 * @param $form
	 */
	function booking_submission( $entry, $form ) {
		global $wpdb;
		//error_log(print_r($entry, true));
		//error_log(print_r($form, true));


		$birthdate = sanitize_text_field(self::field_value_from_class('tmsm-aquatonic-course-birthdate', $form['fields'], $entry));
		$course_start = sanitize_text_field(self::field_value_from_class('tmsm-aquatonic-course-date', $form['fields'], $entry) . ' '.self::field_value_from_class('tmsm-aquatonic-course-hourminutes', $form['fields'], $entry).':00');

		error_log('field firstname: '. self::field_id_from_class('tmsm-aquatonic-course-firstname', $form['fields']));
		error_log('field lastname: '. self::field_id_from_class('tmsm-aquatonic-course-lastname', $form['fields']));
		error_log('value birthdate: '. $birthdate);
		error_log('field email: '. self::field_id_from_class('tmsm-aquatonic-course-email', $form['fields']));
		error_log('field phone: '. self::field_id_from_class('tmsm-aquatonic-course-phone', $form['fields']));
		error_log('field participants: '. self::field_id_from_class('tmsm-aquatonic-course-participants', $form['fields']));
		error_log('field date: '. self::field_id_from_class('tmsm-aquatonic-course-date', $form['fields']));
		error_log('field hourminutes: '. self::field_id_from_class('tmsm-aquatonic-course-hourminutes', $form['fields']));

		error_log('field firstname: '. self::field_value_from_class('tmsm-aquatonic-course-firstname', $form['fields'], $entry));
		error_log('field lastname: '. self::field_value_from_class('tmsm-aquatonic-course-lastname', $form['fields'], $entry));
		error_log('field email: '. self::field_value_from_class('tmsm-aquatonic-course-email', $form['fields'], $entry));
		error_log('field phone: '. self::field_value_from_class('tmsm-aquatonic-course-phone', $form['fields'], $entry));
		error_log('field participants: '. self::field_value_from_class('tmsm-aquatonic-course-participants', $form['fields'], $entry));
		error_log('field course_start: '. $course_start);
		error_log('field hourminutes: '. self::field_value_from_class('tmsm-aquatonic-course-hourminutes', $form['fields'], $entry));

		// Convert birthdate
		if(!empty($birthdate)){
			$objdate = DateTime::createFromFormat( _x( 'm/d/Y', 'birthdate date format for machines', 'tmsm-aquatonic-course-booking' ), $birthdate );
			error_log('birthdate object:');

			error_log(_x( 'mm/dd/yyyy', 'birthdate date format for humans', 'tmsm-aquatonic-course-booking' ));
			error_log(_x( 'm/d/y', 'birthdate date format for machines', 'tmsm-aquatonic-course-booking' ));
			error_log(print_r($objdate, true));
			$birthdate_computed = $objdate->format( 'Y-m-d' ) ?? null;
			error_log('birthdate_computed: '. $birthdate_computed);
		}

		// Calculate date start and end of course
		error_log('courseaverage: '.$this->get_option( 'courseaverage' ));
		$objdate = DateTime::createFromFormat( 'Y-m-d H:i:s', $course_start );
		$objdate->modify( '+' . $this->get_option( 'courseaverage' ) . ' minutes' );
		$course_end = $objdate->format( 'Y-m-d H:i:s' );


		$table = $wpdb->prefix . 'aquatonic_course_booking';
		$data = array(
			'firstname' => self::field_value_from_class('tmsm-aquatonic-course-firstname', $form['fields'], $entry),
			'lastname' => self::field_value_from_class('tmsm-aquatonic-course-lastname', $form['fields'], $entry),
			'email' => self::field_value_from_class('tmsm-aquatonic-course-email', $form['fields'], $entry),
			'phone' => self::field_value_from_class('tmsm-aquatonic-course-phone', $form['fields'], $entry),
			'birthdate' => $birthdate_computed,
			'participants' => self::field_value_from_class('tmsm-aquatonic-course-participants', $form['fields'], $entry),
			'status' => 'active',
			'date_created' => date('Y-m-d H:i:s'),
			'course_start' => $course_start,
			'course_end' => $course_end,
			'author' => get_current_user_id(),
		);

		$format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			);

		$wpdb->insert($table,$data,$format);
		$my_id = $wpdb->insert_id;


	}

	/**
	 * Find the field value with a class in a field list from a Gravity Form
	 *
	 * @param $find_class
	 * @param $fields
	 * @param $entry
	 *
	 * @return string
	 */
	static function field_value_from_class($find_class, $fields, $entry){

		return rgar($entry, self::field_id_from_class($find_class, $fields));

	}

	/**
	 * Find the field id with a class in a field list from a Gravity Form
	 *
	 * @param $find_class
	 * @param $fields
	 *
	 * @return string
	 */
	static function field_id_from_class($find_class, $fields){

		foreach($fields as $field){

			$class = $field['cssClass'];
			if($class === $find_class){
				return $field['id'];

			}
			else{
				if(!empty($field['inputs'])){
					foreach($field['inputs'] as $field_input){
						$class = $field_input['name'];
						if($class === $find_class){
							return $field_input['id'];
						}
					}
				}
			}
		}
	}
	
}
