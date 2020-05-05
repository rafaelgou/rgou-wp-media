<?php
namespace Rgou\WPMedia;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://me.rgou.net
 * @since      1.0.0
 *
 * @package    Rgou/WPMedia
 * @subpackage Rgou/WPMedia/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rgou/WPMedia
 * @subpackage Rgou/WPMedia/admin
 * @author     Rafael Goulart <rafaelgou@rgou.net>
 */
class Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rgou-wp-media-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rgou-wp-media-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Set menu/settings page
	 *
	 * @return void
	 */
	public function menu_init() {
		add_menu_page(
			'RGOU WP-Media',
			'RGOU WP-Media',
			'manage_options',
			'rgou-wp-media',
			array( 'Rgou\WPMedia\Admin', 'admin_init' ),
			'dashicons-yes-alt'
		);
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public static function admin_init() {
		if ( isset( $_POST['config_version'] ) ) {
			/**
			 * This is redundant toi avoid direct form exploitation
			 */
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Unauthorized user' );
			}
			check_admin_referer( 'wp_oil_cookie_consent_option_page_action' );

			self::store( $_POST );
		}

		$values = get_option(
			'rgou_wp_media',
			array(
				'timestamp' => 1,
				'links'     => array(
					'https://google.com',
					'https:/me.rgou.net',
				),
			)
		);

		require_once plugin_dir_path( __FILE__ ) . '../../../admin/partials/rgou-wp-media-admin-display.php';
	}

	/**
	 * Store
	 *
	 * @param array $posted_data Data sent.
	 * @return void
	 */
	public static function store( $posted_data ) {
		$values = get_option( 'rgou_wp_media', false );

		if ( $values ) {
			update_option( 'rgou_wp_media', $posted_data );
		} else {
			add_option( 'rgou_wp_media', $posted_data );
		}
	}
}
