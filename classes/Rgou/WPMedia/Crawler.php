<?php
namespace Rgou\WPMedia;

use DOMDocument;
use DOMImplementation;

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
	 * @param boolean $reload Force reload the content.
	 * @return string
	 */
	public function get_content( $reload = false ) {

		if ( null === $this->content || $reload ) {
			// phpcs:disable
			$this->content = file_get_contents( $this->url );
		}

		return $this->content;
	}

	/**
	 * Parse remote content into DomDocument
	 *
	 * @param  string $html_content The html content.
	 * @return DOMDocument
	 */
	public function get_dom_document( $html_content = null ) {
		if ( null === $html_content ) {
			$html_content = $this->get_content();
		}

		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( $html_content );

		return $doc;
	}

	/**
	 * Get the links.
	 *
	 * @param DOMDocument $doc Dom document.
	 * @return array
	 */
	public function get_links( DOMDocument $doc = null ) {

		if ( null === $doc ) {
			$doc = $this->get_dom_document();
		}

		$nodes = $doc->getElementsByTagName( 'a' );
		$links = [];
		foreach ( $nodes as $node ) {
			if ( strpos( $node->getAttribute( 'href' ), '#' ) === 0 ) {
				continue;
			}

			$links[] = [
				'href'  => $node->getAttribute( 'href' ),
				// phpcs:disable
				'label' => trim( $node->nodeValue ),
			];
		}

		return $links;
	}

	/**
	 * Get the sitemap.
	 *
	 * @param array $links The links.
	 * @return string
	 */
	public function get_sitemap ( $links = null ) {

		if ( null === $links) {
			$links = $this->get_links();
		}
		if ( ! is_array( $links ) ) {
			return null;
		}

		if ( null === $this->sitemap ) {
			$doc = ( new \DOMImplementation() )->createDocument(
				null,
				'html',
				( new \DOMImplementation() )->createDocumentType( 'html' )
			);
			if ( count( $links ) === 0 ) {
				return $this->sitemap;
			}

			$ul = $doc->createElement( 'ul' );
			foreach ( $links as $link ) {
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
			$doc->appendChild( $html );

			$this->sitemap = $doc->saveHTML( $doc );
		}

		return $this->sitemap;
	}
}
