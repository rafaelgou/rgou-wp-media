<?php
namespace Rgou\WPMedia\Tests\Unit\classes;

use Rgou\WPMedia\Tests\Unit\TestCase;
use Rgou\WPMedia\Crawler;
use DOMDocument;

class CrawlerTest extends TestCase
{
    public function test_get_content()
    {
		$crawler = new Crawler('https://www.duckduckgo.com');
		$content = $crawler->get_content();

		$this->assertTrue(strlen( $content ) > 0);
    }

    public function test_get_dom_document()
    {
		$crawler     = new Crawler('https://www.duckduckgo.com');
		$htmlContent = $crawler->get_content();
		$doc         = $crawler->get_dom_document( $htmlContent );

        $this->assertTrue($doc instanceof DOMDocument);
    }

	public function htmlProvider()
    {
        return [
            [
				'<hmtl><body></body></html>',
				0,
				[]
			],
            [
				'<hmtl><body>'
				.'<a href="https://duckduckgo.com">DuckDuckGo</a>'
				.'<a href="https://me.rgou.net">RGOU</a>'
				.'<a href="https://tech.rgou.net">RGOU Tech Blog</a>'
				.'</body></html>',
				3,
				[
					[
						"href"  => 'https://duckduckgo.com',
						"label" => 'DuckDuckGo'
					],
					[
						"href"  => 'https://me.rgou.net',
						"label" => 'RGOU'
					],
					[
						"href"  => 'https://tech.rgou.net',
						"label" => 'RGOU Tech Blog'
					],
				]
			],
        ];
    }

    /**
     * @dataProvider htmlProvider
     */
	public function test_get_links( $htmlContent, $count, $expectedLinks )
    {
		$crawler = new Crawler('');
		$doc     = $crawler->get_dom_document( $htmlContent );
		$links   = $crawler->get_links( $doc );

        $this->assertSame( count( $links) , $count );

		foreach ($expectedLinks as $key => $expectedLink) {
			$this->assertSame(
				$expectedLink['href'],
				$links[ $key ]['href']
			);
			$this->assertSame(
				$expectedLink['label'],
				$links[ $key ]['label']
			);
		}
    }

    /**
     * @dataProvider htmlProvider
     */
    public function test_get_sitemap( $htmlContent, $count, $expectedLinks )
    {
		$crawler     = new Crawler('');
		$doc         = $crawler->get_dom_document( $htmlContent );
		$links       = $crawler->get_links( $doc );
		$sitemap     = $crawler->get_sitemap( $links );

		if ( $count === 0 ) {
			$this->assertNull( $sitemap );
		} else {
			$docSitemap  = $crawler->get_dom_document( $sitemap );
			$nodes = $docSitemap->getElementsByTagName('a');

			$this->assertSame( count( $nodes) , $count );

			foreach ($nodes as $key => $node) {
				$this->assertSame(
					$node->getAttribute('href'),
					$expectedLinks[ $key ]['href']
				);
				$this->assertSame(
					$node->nodeValue,
					$expectedLinks[ $key ]['label']
				);
			}
		}
    }
}
