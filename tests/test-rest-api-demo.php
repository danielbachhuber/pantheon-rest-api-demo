<?php
/**
 * Class Tests_REST_API_Demo
 *
 * @package Rest_Api_Demo
 */

/**
 * Testcases for the REST API demo plugin
 */
class Tests_REST_API_Demo extends WP_UnitTestCase {

	function test_format_phone_number() {
		$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '555-212-2121' ) );
		$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '5552122121' ) );
		$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '+1 (555) 212 2121' ) );
		$this->assertEquals( '', rad_format_phone_number( '' ) );
	}

}
