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

		try {
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

			$rgou_wp_media_values = get_option(
				'rgou_wp_media',
				[
					'timestamp' => false,
					'links'     => [],
				]
			);

		} catch ( \Exception $e ) {
			delete_option( 'rgou_wp_media' );
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Error!', 'rgou-wp-media' ); ?> <?php echo esc_html( $e->getMessage() ); ?></p>
			</div>
			<?php
		}

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
	 * @throws \Exception Unable to create file.
	 */
	protected static function dump_files( Crawler $crawler ) {
		$fs = self::get_wp_filesystem_direct();
		if ( ! $fs->put_contents( get_home_path() . 'index.html', $crawler->get_content( true ), self::get_chmod() ) ) {
			// translators:Home path.
			$msg = sprintf( __( 'Unable to create %s index.html. Please check root folder permissions.', 'rgou-wp-media' ), get_home_path() );
			throw new \Exception( $msg );
		};
		if ( ! $fs->put_contents( get_home_path() . 'sitemap.html', $crawler->get_sitemap(), self::get_chmod() ) ) {
			// translators:Home path.
			$msg = sprintf( __( 'Unable to create %s sitemap.html. Please check root folder permissions.', 'rgou-wp-media' ), get_home_path() );
			throw new \Exception( $msg );
		};
	}

	/**
	 * Dump files using the crawler
	 *
	 * @return void
	 * @throws \Exception Unable to create file.
	 */
	protected static function delete_files() {
		$fs = self::get_wp_filesystem_direct();
		if ( ! $fs->delete( get_home_path() . 'index.html', self::get_chmod() ) ) {
			// translators:Home path.
			$msg = sprintf( __( 'Unable to remove %s index.html. Please check root folder permissions.', 'rgou-wp-media' ), get_home_path() );
			throw new \Exception( $msg );
		};
		if ( ! $fs->delete( get_home_path() . 'sitemap.html', self::get_chmod() ) ) {
			// translators:Home path.
			$msg = sprintf( __( 'Unable to remove %s sitemap.html. Please check root folder permissions.', 'rgou-wp-media' ), get_home_path() );
			throw new \Exception( $msg );
		};
	}

}
