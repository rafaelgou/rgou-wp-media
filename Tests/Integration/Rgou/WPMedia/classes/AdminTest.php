<?php
namespace Rgou\WPMedia\Tests\Unit\classes;

use Rgou\WPMedia\Tests\Integration\TestCase;
use Rgou\WPMedia\Admin;

class AdminTest extends TestCase
{
    public function test_sample()
    {
		Admin::crawler();

		$this->assertTrue( false );
    }
}
