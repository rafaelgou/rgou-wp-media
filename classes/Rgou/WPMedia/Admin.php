<?php
namespace Rgou\WPMedia;

use WP_Error;

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rgou-wp-media-admin.css', [], $this->version, 'all' );

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rgou-wp-media-admin.js', [ 'jquery' ], $this->version, false );

	}

	/**
	 * Set menu/settings page
	 *
	 * @return void
	 */
	public function settings_init() {
		add_options_page(
			'RGOU Sitemap',
			'RGOU Sitemap',
			'manage_options',
			'rgou-wp-media',
			[ 'Rgou\WPMedia\Admin', 'admin_init' ]
		);
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public static function admin_init() {
		/**
		 * This is redundant to avoid direct form exploitation
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized user' );
		}
		if ( isset( $_POST['submit'] ) || isset( $_POST['disable'] ) ) {
			check_admin_referer( 'rgou_wp_media_option_page_action' );

			if ( isset( $_POST['submit'] ) ) {
				self::crawler();
				self::schedule_crawler();
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Done! Next run in one hour', 'rgou-wp-media' ); ?></p>
				</div>
				<?php
			} elseif ( isset( $_POST['disable'] ) ) {
				self::disable_crawler();
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Done! Sitemap disabled', 'rgou-wp-media' ); ?></p>
				</div>
				<?php
			}
		}

		$values = get_option(
			'rgou_wp_media',
			[
				'timestamp' => wp_next_scheduled( 'rgou_wp_media_crawler' ),
				'links'     => [],
			]
		);

		require_once plugin_dir_path( __FILE__ ) . '../../../admin/partials/rgou-wp-media-admin-display.php';
	}

	/**
	 * Run the Crowler
	 *
	 * @return void
	 */
	public static function crawler() {
		$url        = get_site_url();
		$crawler    = new Crawler( $url );
		$links      = $crawler->get_links();
		$values     = get_option( 'rgou_wp_media', false );
		$new_values = [
			'timestamp' => ( new \DateTime() )->format( 'YmdHis' ),
			'links'     => $links,
		];

		if ( $values ) {
			update_option( 'rgou_wp_media', $new_values );
		} else {
			add_option( 'rgou_wp_media', $new_values );
		}

		self::dump_files( $crawler );
	}

	/**
	 * Disable crawler
	 *
	 * @return void
	 */
	public static function disable_crawler() {
		$values     = get_option( 'rgou_wp_media', false );
		$new_values = [
			'timestamp' => false,
			'links'     => [],
		];

		if ( $values ) {
			update_option( 'rgou_wp_media', $new_values );
		} else {
			add_option( 'rgou_wp_media', $new_values );
		}

		if ( wp_next_scheduled( 'rgou_wp_media_crawler' ) ) {
			$timestamp = wp_next_scheduled( 'rgou_wp_media_crawler' );
			wp_unschedule_event( $timestamp, 'rgou_wp_media_crawler' );
		}

		self::delete_files();
	}

	/**
	 * Schedule crawler to run
	 *
	 * @return void
	 */
	public static function schedule_crawler() {
		if ( wp_next_scheduled( 'rgou_wp_media_crawler' ) ) {
			$timestamp = wp_next_scheduled( 'rgou_wp_media_crawler' );
			wp_unschedule_event( $timestamp, 'rgou_wp_media_crawler' );
		}
		wp_schedule_event( time(), 'hourly', 'rgou_wp_media_crawler' );
	}

	/**
	 * Load WP_Filesystem_Direct
	 *
	 * @return WP_Filesystem_Direct
	 */
	protected static function get_wp_filesystem_direct() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		return new \WP_Filesystem_Direct( new \StdClass() );
	}

	/**
	 * Get chmod
	 *
	 * @return int
	 */
	protected static function get_chmod() {
		if ( defined( 'FS_CHMOD_FILE' ) ) {
			return FS_CHMOD_FILE;
		}

		return fileperms( ABSPATH . 'index.php' ) & 0777 | 0644;
	}

	/**
	 * Dump files using the crawler
	 *
	 * @param Crawler $crawler The crawler.
	 * @return void
	 */
	protected static function dump_files( Crawler $crawler ) {
		$fs = self::get_wp_filesystem_direct();
		$fs->put_contents( get_home_path() . '/index.html', $crawler->get_content( true ), self::get_chmod() );
		$fs->put_contents( get_home_path() . '/sitemap.html', $crawler->get_sitemap(), self::get_chmod() );
	}

	/**
	 * Dump files using the crawler
	 *
	 * @return void
	 */
	protected static function delete_files() {
		$fs = self::get_wp_filesystem_direct();
		$fs->delete( get_home_path() . '/index.html', self::get_chmod() );
		$fs->delete( get_home_path() . '/sitemap.html', self::get_chmod() );
	}

}
