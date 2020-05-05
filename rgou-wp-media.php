<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://me.rgou.net
 * @since             1.0.0
 * @package           Rgou_Wp_Media
 *
 * @wordpress-plugin
 * Plugin Name:       RGOU WP-Media
 * Plugin URI:        https://github.com/rafaelgou/rgou-wp-media
 * Description:       Homepage Crawler for WP-Media Technical Assessment
 * Version:           1.0.0
 * Author:            Rafael Goulart
 * Author URI:        https://me.rgou.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rgou-wp-media
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RGOU_WP_MEDIA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in classes/Rgou/WPMedia/activator.php
 */
function rgou_wp_media_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/Activator.php';
	Rgou_Wp_Media_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in classes/Rgou/WPMedia/Deactivator.php
 */
function rgou_wp_media_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/Deactivator.php';
	Rgou_Wp_Media_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'rgou_wp_media_activate' );
register_deactivation_hook( __FILE__, 'rgou_wp_media_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/Admin.php';
require plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/I18n.php';
require plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/Loader.php';
require plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/Plugin.php';
require plugin_dir_path( __FILE__ ) . 'classes/Rgou/WPMedia/PublicArea.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function rgou_wp_media_run() {

	$plugin = new Rgou\WPMedia\Plugin();
	$plugin->run();

}
rgou_wp_media_run();
