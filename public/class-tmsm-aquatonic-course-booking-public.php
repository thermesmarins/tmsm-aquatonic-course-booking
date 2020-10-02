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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-course-booking-public.js', array( 'wp-backbone', 'moment', 'jquery' ), $this->version, true );

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
					<option value="publish"<# if ( data.status == 'publish' ) { #> SELECTED<# } #>><?php echo esc_html( __( 'Published', 'backbone-example' ) ) ?></option>
					<option value="draft"<# if ( data.status == 'draft' ) { #> SELECTED<# } #>><?php echo esc_html( __( 'Draft', 'backbone-example' ) ) ?></option>
				</select>
			</td>
			<td>
				<button class="button save"><?php echo esc_html( __( 'Save', 'backbone-example' ) ) ?></button>
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
				<th><?php echo esc_html( __( 'Title', 'backbone-example' ) ) ?></th>
				<th><?php echo esc_html( __( 'Publish Status', 'backbone-example' ) ) ?></th>
				<th><?php echo esc_html( __( 'Action', 'backbone-example' ) ) ?></th>
				<th>{{ moment().add(0, 'days').format('MMMM Do YYYY, h:mm:ss a') }}</th>
				</thead>
				<tbody class="bb-posts"></tbody>
			</table>

			<p><button class="button button-primary refresh"><?php echo esc_html( __( 'Refresh', 'backbone-example' ) ) ?></button></p>
			<p><button class="btn-default previous"><?php echo esc_html( __( 'Previous', 'backbone-example' ) ) ?></button></p>
			<p><button class="btn-default next"><?php echo esc_html( __( 'Next', 'backbone-example' ) ) ?></button></p>
		</script>
		<?php
	}

	
}
