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
 * The Crawler.
 *
 * @package    Rgou/WPMedia
 * @author     Rafael Goulart <rafaelgou@rgou.net>
 */
class Crawler {

	/**
	 * The URL to be crawled.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $url    The URL to be crawled
	 */
	private $url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param  string $url The URL to be crawled.
	 */
	public function __construct( $url ) {

		$this->url = $url;

	}

	/**
	 * Get the page content.
	 *
	 * @return string
	 */
	public function get_page_content() {

		return '';

	}

	/**
	 * Get the links.
	 *
	 * @return links
	 */
	public function get_links() {

		return array();

	}

	/**
	 * Get the sitemap.
	 *
	 * @return string
	 */
	public function get_sitemap() {

		return '';

	}

	/**
	 * Run
	 *
	 * @return void
	 */
	public function run() {

	}

}
