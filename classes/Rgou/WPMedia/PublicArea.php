<?php
namespace Rgou\WPMedia;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://me.rgou.net
 * @since      1.0.0
 *
 * @package    Rgou/WPMedia
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rgou/WPMedia
 * @author     Rafael Goulart <rafaelgou@rgou.net>
 */
class PublicArea {

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
	 * @param  string $plugin_name The name of the plugin.
	 * @param  string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Add footer filter
	 *
	 * @return void
	 */
	public function add_footer() {
		require_once plugin_dir_path( __FILE__ ) . '../../../public/partials/rgou-wp-media-public-display.php';
	}
}
