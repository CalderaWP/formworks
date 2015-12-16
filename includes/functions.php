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

/**
 * Featured plugins from CalderaWP display
 *
 * @since 1.0.0
 *
 * @return string
 */
function frmwks_cwp_featured(){
	if(  false == ( $out = get_transient( md5( __FUNCTION__ ) ) ) ) {
		$r = wp_remote_get( 'https://calderawp.com/wp-json/calderawp_api/v2/products/featured' );
		if( ! is_wp_error( $r ) ) {
			$plugins = json_decode( wp_remote_retrieve_body( $r ) );

			foreach( $plugins as $id => $plugin ){
				if( 10288 == $id ) {
					continue;
				}

				$button = __( 'Learn More', 'formworks' );
				if( isset( $plugin->prices ) && ! empty( $plugin->prices ) ) {
					$prices = (array) $plugin->prices;
					$prices = array_values( $prices );

					if( ! empty( $prices ) && isset( $prices[0], $prices[0]->amount)  ) {
						$button = __( 'From', 'formworks' ) . ' $' . $prices[0]->amount;
					}
				}

				$string = '
					<div class="panel_{{slug}}" style="margin: 10px; width: 300px; float: left; height:250px;overflow: auto; border: 1px solid rgba(0, 0, 0, 0.15); box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);position: relative;">
						<img src="{{banner}}" style="width:100%;vertical-align: top;">
							<p style="padding: 8px;">{{tagline}}</p>
							<div style="margin: 0px; padding: 6px 7px;"></div>
								<div style="position: absolute; bottom: 0px; padding: 6px; background: none repeat scroll 0 0 rgba(0, 0, 0, 0.03); left: 0px; right: 0px; border-top: 1px solid rgba(0, 0, 0, 0.06);">
								<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
								<a class="button" href="{{link}}" target="_blank" rel="nofollow" style="float:right">{{button}}</a>
								</div>

							</div>
						</div>';
				$out[] = str_replace(
					array(
						'{{slug}}',
						'{{banner}}',
						'{{link}}',
						'{{tagline}}',
						'{{button}}'
					),
					array(
						sanitize_title( $plugin->name ),
						$plugin->image_src,
						esc_url( $plugin->link ),
						esc_html( $plugin->tagline ),
						esc_html( $button )
					),
				$string );
			}

			$out = implode( '', $out );
			set_transient( md5( __FUNCTION__ ), $out, HOUR_IN_SECONDS );
		}
	}

	return $out;
}

/**
 * Software Licensing arguments
 *
 * @since 1.0.0
 */
function frmwks_licensing_args() {
	$plugin = array(
		'name'		=>	'FormWorks',
		'slug'		=>	'formworks',
		'url'		=>	'https://calderawp.com/downloads/formworks',
		'version'	=>	FRMWKS_VER,
		'key_store'	=>  'frmwrks_key',
		'file'		=>  FRMWKS_CORE
	);

	return $plugin;
}

/**
 * Display software licensing -- needs active, is active.
 *
 * @since 1.0.0
 *
 * @return string
 */
function frmwks_license_display(){
	$plugin = frmwks_licensing_args();
	if( ! function_exists( 'cwp_license_manager_register_licensed_product' ) ) {
		$out[] = esc_html__( 'To activate or check the status of your FormWorks license, you must intall CalderaWP License Manager.', 'formworks' );
		$out[] = sprintf( '<p><a class="button button-secondary" href="%s" target="_blank">%s</a></p>',
							esc_url_raw( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=calderawp-license-manager' ), 'install-plugin_calderawp-license-manager' ) ),
							esc_html__( 'Install The License Manager', 'formworks' )
		);


	}else{
		$active = cwp_license_manager_is_product_licensed( $plugin[ 'name'] );
		if( $active ) {
			$out[] = '<p style="border-left: 4px solid #46b450;background: white;display: inline;padding: 12px;">' . esc_html__( 'Your license is active', 'formworks' ) . '</p>';
		}elseif( ! $active  ) {
			$out[] = sprintf( '<a class="button button-secondary" href="%s" target="_blank">%s</a>',
						self_admin_url( 'options-general.php?page=calderawp_license_manager' ),
						esc_html__( 'Please activate your FormWorks license using CalderaWP License Manager.', 'formworks' )
			);
		}
	}

	return implode( "\n", $out );


}

