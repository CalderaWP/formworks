<?php
/**
 * Loads the plugin if dependencies are met.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer & CalderaWP LLC
 */


if ( file_exists( FRMWKS_PATH . 'vendor/autoload.php' ) ){
	//autoload dependencies
	require_once( FRMWKS_PATH . 'vendor/autoload.php' );

	// initialize plugin
	\calderawp\frmwks\core::get_instance();

}else{
	return new WP_Error( 'formworks--no-dependencies', __( 'Dependencies for Formworks could not be found.', 'formworks' ) );
}


