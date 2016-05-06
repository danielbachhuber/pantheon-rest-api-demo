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

		$this->subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function test_get_unauthorized() {
		wp_set_current_user( 0 );
		$request = new WP_REST_Request( 'GET', '/rad/v1/site-info' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 401, $response );
	}

	public function test_get_authorized() {
		wp_set_current_user( $this->subscriber );
		$request = new WP_REST_Request( 'GET', '/rad/v1/site-info' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '(555) 212-2121',
		), $response );
	}

	public function test_get_authorized_reformatted() {
		update_option( 'phone_number', '555 555 5555' );
		wp_set_current_user( $this->subscriber );
		$request = new WP_REST_Request( 'GET', '/rad/v1/site-info' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '(555) 555-5555',
		), $response );
	}

	public function test_get_authorized_invalid_format() {
		update_option( 'phone_number', 'will this work?' );
		wp_set_current_user( $this->subscriber );
		$request = new WP_REST_Request( 'GET', '/rad/v1/site-info' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '',
		), $response );
	}

	public function test_update_unauthorized() {
		wp_set_current_user( $this->subscriber );
		$request = new WP_REST_Request( 'POST', '/rad/v1/site-info' );
		$request->set_param( 'phone_number', '(111) 222-3333' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 403, $response );
		$this->assertEquals( '(555) 212-2121', get_option( 'phone_number' ) );
	}

	public function test_update_authorized() {
		wp_set_current_user( $this->administrator );
		$request = new WP_REST_Request( 'POST', '/rad/v1/site-info' );
		$request->set_param( 'phone_number', '(111) 222-3333' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '(111) 222-3333',
		), $response );
		$this->assertEquals( '(111) 222-3333', get_option( 'phone_number' ) );
	}

	public function test_update_authorized_reformatted() {
		wp_set_current_user( $this->administrator );
		$request = new WP_REST_Request( 'POST', '/rad/v1/site-info' );
		$request->set_param( 'phone_number', '555 555 5555' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '(555) 555-5555',
		), $response );
		$this->assertEquals( '(555) 555-5555', get_option( 'phone_number' ) );
	}

	public function test_update_authorized_empty() {
		wp_set_current_user( $this->administrator );
		$request = new WP_REST_Request( 'POST', '/rad/v1/site-info' );
		$request->set_param( 'phone_number', '' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 200, $response );
		$this->assertResponseData( array(
			'phone_number' => '',
		), $response );
		$this->assertEquals( '', get_option( 'phone_number' ) );
	}

	public function test_update_authorized_invalid_format() {
		wp_set_current_user( $this->administrator );
		$request = new WP_REST_Request( 'POST', '/rad/v1/site-info' );
		$request->set_param( 'phone_number', 'will this work?' );
		$response = $this->server->dispatch( $request );
		$this->assertResponseStatus( 400, $response );
		$this->assertResponseData( array(
			'message' => 'Invalid parameter(s): phone_number',
		), $response );
		$this->assertEquals( '(555) 212-2121', get_option( 'phone_number' ) );
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
