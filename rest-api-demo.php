<?php
/**
 * Plugin Name:     REST API Demo
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Daniel Bachhuber, Pantheon
 * Author URI:      https://handbuilt.co
 * Text Domain:     rest-api-demo
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Rest_Api_Demo
 */


function rad_format_phone_number( $input ) {
	if ( preg_match( '#([\d]{3})[^\d]*([\d]{3})[^\d]*([\d]{4})#', $input, $matches ) ) {
		return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
	}
	return '';
}

add_action( 'rest_api_init', function() {

	register_rest_route( 'rad/v1', 'site-info', array(
		array(
			'methods'             => 'GET',
			'callback'            => function( $request ) {
				return array(
					'phone_number' => get_option( 'phone_number' ),
				);
			},
			'permission_callback' => function() {
				if ( is_user_logged_in() ) {
					return true;
				}
				return new WP_Error(
					'rad_unauthorized',
					'You are not authorized to view this resource.',
					array( 'status' => 401 ) );
			},
		),
		array(
			'methods'            => 'POST',
			'callback'           => function( $request ) {
				update_option( 'phone_number', $request['phone_number'] );
				return array(
					'phone_number' => get_option( 'phone_number' ),
				);
			},
			'permission_callback' => function() {
				if ( current_user_can( 'manage_options' ) ) {
					return true;
				}
				return new WP_Error(
					'rad_unauthorized',
					'You are not authorized to update this resource.',
					array( 'status' => is_user_logged_in() ? 403 : 401 ) );
			},
		),
	) );

} );
