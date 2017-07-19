<?php

/**
 * Helper functions forgettign map and report information via API requests.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Send requests for info to the maps/reports JSON Service.
 *
 * @since 1.0.0
 *
 * @param 	$item_type 	string 	map|report|area
 * @param 	$group_id 	int
 * @param 	$user_id 	int
 * @param 	$search 	string 	search terms
 * @param 	$item_id 	bool 	ID of individual item. Must be used in combination with $item_type.
 *
 * @return 	array of arrays
 */
function cares_maps_json_svc_make_request( $item_type, $group_id = false, $user_id = false, $search = false, $item_id = false, $featured = false ) {
	if ( ! in_array( $item_type, array( 'map', 'report', 'area' ) ) ) {
		return false;
	}

	// Use production or test value of API endpoint?
	$uri = cares_maps_json_svc_base_url() . "services/usercontent/savedcontent.svc/";

	$query_args = array( 'itemtype' => $item_type );

	if ( $group_id ) {
		$query_args['hubid'] = $group_id;
	}
	if ( $user_id ) {
		$query_args['userid'] = $user_id;
	}
	if ( $search ) {
		$query_args['keywords'] = $search;
	}
	if ( $item_id ) {
		$query_args['itemid'] = $item_id;
	}
	if ( $featured ) {
		$query_args['featured'] = 1;
	}

	$uri = add_query_arg( $query_args, $uri );

	$response = wp_remote_get( $uri );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	if ( $body = wp_remote_retrieve_body( $response ) ) {
		$decoded = json_decode( $body, true );
		if ( $item_id ) {
			// For single results, only return the result--no wrapper array.
			return $decoded[0];
		} else {
			return $decoded;
		}
	} else {
		return false;
	}
}

/**
 * Calculate the base url to use for JSON service requests.
 *
 * @since 1.0.0
 *
 * @return string
 */
function cares_maps_json_svc_base_url() {
	// We use the staging site for testing, use the real maps environment for production.
	$location = get_site_url( null, '', 'http' );
	switch ( $location ) {
		case 'http://staging.communitycommons.org':
			// Testing value:
			$base_url = 'https://staging.maps.communitycommons.org/';
			break;
		default:
			// Production value:
			$base_url = 'https://maps.communitycommons.org/';
			break;
	}
	return apply_filters( 'cares_maps_json_svc_base_url', $base_url );
}
