<?php
namespace Rgou\WPMedia;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://me.rgou.net
 * @since      1.0.0
 *
 * @package    Rgou/WPMedia
 * @subpackage Rgou/WPMedia/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Rgou/WPMedia
 * @subpackage Rgou/WPMedia/includes
 * @author     Rafael Goulart <rafaelgou@rgou.net>
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rgou-wp-media',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/../../languages/'
		);

	}



}
