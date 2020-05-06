<?php
namespace Rgou\WPMedia;

use DOMDocument;
use DOMXpath;

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
	 * @var      string $url The URL to be crawled
	 */
	private $url;

	/**
	 * DEOM Document
	 *
	 * @since  0.1.0
	 * @access private
	 * @var DomDocument $doc The DOM document
	 */
	private $doc;

	/**
	 * The URL content
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $content The URL content
	 */
	private $content;

	/**
	 * The Sitemap content
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $sitemap The Sitemap content
	 */
	private $sitemap;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since  1.0.0
	 * @param  string $url The URL to be crawled.
	 */
	public function __construct( $url ) {

		$this->url = $url;
	}

	/**
	 * Get the page content.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_content() {

		if ( null === $this->content ) {
			// phpcs:disable
			$this->content = file_get_contents( $this->url );
		}

		return $this->content;
	}

	/**
     * Parse remote content into DomDocument
	 *
     * @param  string $htmlContent
     * @return DOMDocument
     */
    public function get_dom_document( $htmlContent )
    {

        $doc = new DOMDocument;
        libxml_use_internal_errors( true );
		$doc->loadHTML( $htmlContent );

        return $doc;
    }

	/**
	 * Get the links.
	 *
	 * @param DOMDocument $doc Dom document
	 *
	 * @return links
	 */
	public function get_links(DOMDocument $doc) {

		$nodes = $doc->getElementsByTagName('a');
		$links = [];
        foreach ($nodes as $node) {
            $links[] = [
				"href"  => $node->getAttribute('href'),
				"label" => $node->nodeValue
			];
		}

		return $links;
	}

	/**
	 * Get the sitemap.
	 *
	 * @return string
	 */
	public function get_sitemap( $links ) {

		if ( ! is_array( $links ) ) {
			return null;
		}

		if ( null === $this->sitemap ) {
			$doc = new DOMDocument;
			if ( count( $links ) === 0 ) {
				return $this->sitemap;
			}

			$ul = $doc->createElement( 'ul' );
			foreach( $links as $link ) {
				$li = $doc->createElement( 'li' );
				$a  = $doc->createElement( 'a', $link['label'] );
				$a->setAttribute( 'href', $link['href'] );
				$a->setAttribute( 'target', '_blank' );
				$li->appendChild( $a );
				$ul->appendChild( $li );
			}
			$body = $doc->createElement( 'body' );
			$h1   = $doc->createElement( 'h1', 'Sitemap' );
			$body->appendChild( $h1 );
			$body->appendChild( $ul );

			$css = $doc->createElement( 'link' );
			$css->setAttribute( 'rel', 'stylesheet' );
			$css->setAttribute( 'href', 'https://cdn.jsdelivr.net/npm/@ajusa/lit@latest/dist/lit.css' );

			$head = $doc->createElement( 'head' );
			$head->appendChild( $css );

			$html = $doc->createElement( 'html' );
			$html->appendChild( $head );
			$html->appendChild( $body );

			$this->sitemap = $doc->saveHTML( $html );
		}

		return $this->sitemap;
	}

	/**
	 * Run
	 *
	 * @return void
	 */
	public function run() {

	}

}
