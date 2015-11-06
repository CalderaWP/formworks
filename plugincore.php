<?php
/**
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 David Cramer & CalderaWP LLC
 *
 * @wordpress-plugin
 * Plugin Name: Formworks
 * Plugin URI:  http://CalderaWP.com
 * Description: Form analytics for your favorite WordPress Form Builder Plugins
 * Version: 1.0.0-b-2
 * Author:      David Cramer
 * Author URI:  https://CalderaWP.com
 * Text Domain: formworks
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('FRMWKS_PATH',  plugin_dir_path( __FILE__ ) );
define('FRMWKS_CORE',  __FILE__ );
define('FRMWKS_SLUG',  'frmwrks-trk' );
define('FRMWKS_URL',  plugin_dir_url( __FILE__ ) );
define( 'FRMWKS_VER', '1.0.0-b-2' );



// Load instance
add_action( 'plugins_loaded', 'frmwks_bootstrap' );
function frmwks_bootstrap(){

	if ( is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		include_once FRMWKS_PATH . 'vendor/calderawp/dismissible-notice/src/functions.php';
	}


	if ( ! version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
		if ( is_admin() ) {
			
			//BIG nope nope nope!
			$message = __( sprintf( 'Formworks requires PHP version %1s or later. We strongly recommend PHP 5.5 or later for security and performance reasons. Current version is %2s.', '5.3.0', PHP_VERSION ), 'formworks' );
			echo caldera_warnings_dismissible_notice( $message, true, 'activate_plugins' );
		}

	}else{
		//bootstrap plugin
		require_once( FRMWKS_PATH . 'bootstrap.php' );

	}

}
// include helpers
include_once FRMWKS_PATH . 'includes/functions.php';
