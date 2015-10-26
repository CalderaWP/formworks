<?php
/**
 * Functions for this plugin
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer & CalderaWP LLC
 */

// activate - table and version check
register_activation_hook( FRMWKS_CORE , 'activate_formworks_tracker' );

/**
 * Activate and setup formworks tracker
 */
function activate_formworks_tracker(){
	global $wpdb;

	$version = get_option('_formworks_tracker');

	if(!empty($version) ){
		if( version_compare($version, FRMWKS_VER) === 0 ){ // no change
			//echo version_compare('1.1.1', FRMWKS_VER);
			return;
		}
	}

	$tables = $wpdb->get_results("SHOW TABLES", ARRAY_A);
	foreach($tables as $table){
		$alltables[] = implode($table);
	}

	// meta table
	if(!in_array($wpdb->prefix.'formworks_tracker', $alltables)){
		// create meta tables
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$meta_table = "CREATE TABLE `" . $wpdb->prefix . "formworks_tracker` (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`form_id` varchar(18) DEFAULT NULL,
		`prefix` VARCHAR(18) DEFAULT NULL,
		`user_id` int(11) DEFAULT NULL,
		`user_key` varchar(41) DEFAULT NULL,
		`datestamp` datetime DEFAULT NULL,
		`meta_key` varchar(41) DEFAULT NULL,
		`meta_value` LONGTEXT DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `form_id` (`form_id`),
		KEY `user_id` (`user_id`),
		KEY `datestamp` (`datestamp`),
		KEY `user_key` (`user_key`)
		) DEFAULT CHARSET=utf8;";
		
		dbDelta( $meta_table );

	}

	// push to record version DB changes
	 update_option( '_formworks_tracker', FRMWKS_VER );
}
