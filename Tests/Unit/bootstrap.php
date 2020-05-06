<?php
/**
 * Bootstraps theUnit Tests
 *
 * @package Rgou\WPMedia\Tests\Unit
 */

namespace Rgou\WPMedia\Tests\Unit;

use function Rgou\WPMedia\Tests\init_test_suite;

require_once dirname( dirname( __FILE__ ) ) . '/bootstrap-functions.php';
init_test_suite( 'Unit' );
