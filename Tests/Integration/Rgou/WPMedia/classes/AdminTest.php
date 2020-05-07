<?php
namespace Rgou\WPMedia\Tests\Unit\classes;

use Rgou\WPMedia\Tests\Integration\TestCase;
use Rgou\WPMedia\Admin;
use DOMDocument;

class AdminTest extends TestCase
{
	public function setUp() {
		if ( file_exists( get_home_path() . '/index.html' ) ) {
			unlink( get_home_path() . '/index.html' );
		}
		if ( file_exists( get_home_path() . '/sitemap.html' ) ) {
			unlink( get_home_path() . '/sitemap.html' );
		}
	}

	public function tearDown() {
		if ( file_exists( get_home_path() . '/index.html' ) ) {
			unlink( get_home_path() . '/index.html' );
		}
		if ( file_exists( get_home_path() . '/sitemap.html' ) ) {
			unlink( get_home_path() . '/sitemap.html' );
		}
	}

    public function test_run_crawler()
    {
		Admin::crawler();
		$values = get_option( 'rgou_wp_media', false );

		$this->assertTrue( isset( $values['links'] ) );
		$this->assertTrue( count( $values['links'] ) > 0);
		$this->assertTrue( file_exists( get_home_path() . '/index.html' ) );
		$this->assertTrue( file_exists( get_home_path() . '/sitemap.html' ) );
    }

    public function test_disable_crawler()
    {
		Admin::crawler();
		Admin::disable_crawler();
		$values = get_option( 'rgou_wp_media', false );

		$this->assertTrue( isset( $values['links'] ) );
		$this->assertTrue( count( $values['links'] ) === 0);
		$this->assertTrue( ! file_exists( get_home_path() . '/index.html' ) );
		$this->assertTrue( ! file_exists( get_home_path() . '/sitemap.html' ) );
    }

}
