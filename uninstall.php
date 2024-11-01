<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://uniquehosting.net/
 * @since      1.0.0
 *
 * @package    WarmupReps
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option('wmp_default_data_imported');
	global $wpdb;
	$tables = array();
	$tables[] = $wpdb->prefix . 'wmp_workout';
	$tables[] = $wpdb->prefix . 'wmp_excercise';
	foreach($tables as $table){
    	$wpdb->query("DROP TABLE IF EXISTS $table");
	}
$allposts= get_posts( array('post_type'=>'wmp-program','numberposts'=>-1) );
foreach ($allposts as $eachpost) {
wp_delete_post( $eachpost->ID, true );
}
