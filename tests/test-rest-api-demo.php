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

	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		update_option( 'phone_number', '(555) 212-2121' );
	}

	public function test_get() {
		$request = new WP_REST_Request( 'GET', '/rad/v1/site-info' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '(555) 212-2121',
		), $response );
	}

	function test_format_phone_number() {
		$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '555-212-2121' ) );
		$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '5552122121' ) );
		$this->assertEquals( '(555) 212-2121', rad_format_phone_number( '+1 (555) 212 2121' ) );
		$this->assertEquals( '', rad_format_phone_number( '' ) );
	}

	protected function assertResponseStatus( $status, $response ) {
		$this->assertEquals( $status, $response->get_status() );
	}

	protected function assertResponseData( $data, $response ) {
		$response_data = $response->get_data();
		$tested_data = array();
		foreach( $data as $key => $value ) {
			if ( isset( $response_data[ $key ] ) ) {
				$tested_data[ $key ] = $response_data[ $key ];
			} else {
				$tested_data[ $key ] = null;
			}
		}
		$this->assertEquals( $data, $tested_data );
	}

	public function tearDown() {
		parent::tearDown();

		global $wp_rest_server;
		$wp_rest_server = null;
	}

}
