<?php
namespace Rgou\WPMedia;

/**
 * Fired during plugin uninstall
 *
 * @link       https://me.rgou.net
 * @since      1.0.0
 *
 * @package    Rgou/WPMedia
 * @subpackage Rgou/WPMedia/includes
 */

/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run during the plugin's uninstall.
 *
 * @since      1.0.0
 * @package    Rgou/WPMedia
 * @subpackage Rgou/WPMedia/includes
 * @author     Rafael Goulart <rafaelgou@rgou.net>
 */
class Uninstall {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {
		delete_option( 'rgou_wp_media' );
		if ( wp_next_scheduled( 'rgou_wp_media_crawler' ) ) {
			$timestamp = wp_next_scheduled( 'rgou_wp_media_crawler' );
			wp_unschedule_event( $timestamp, 'rgou_wp_media_crawler' );
		}
		Admin::delete_files();
	}
}
