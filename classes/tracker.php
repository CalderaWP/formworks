<?php
/**
 * Formworks Tracker.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

namespace calderawp\frmwks;

/**
 * Tracker class.
 *
 * @package Formworks
 * @author  David Cramer
 */
class tracker {

	/**
	 * Retrieve the user_key
	 *
	 * @since 1.0.0
	 *
	 * @return int current stat ID
	 */
	public static function get_user_key(){
		$track_id = 0;
		// return usertag only if there is one
		if( !empty( $_COOKIE[ FRMWKS_SLUG ] ) ){
			$track_id = $_COOKIE[ FRMWKS_SLUG ];
		}else{
			// alternate - the key was created on this load so it's probably not available yet, but we make it global for this run
			global $formworks_current_usertag;
			if( !empty( $formworks_current_usertag ) ){
				$track_id = $formworks_current_usertag;
			}
		}
		return $track_id;
	}


	/**
	 * Get an activity line for a slug
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix tracking prefix
	 * @param string $days number of days
	 *
	 * @return void
	 */
	public static function get_activity( $prefix, $days = 6 ){
		return '23';
		global $wpdb;
		$query = $wpdb->prepare("
			SELECT COUNT(`id`) AS `total` FROM `" . $wpdb->prefix . "formworks_tracker` WHERE `prefix` = %s && 
			GROUP BY SUBSTR( `datestamp`,1,7);
		", $prefix );

		return self::save( $type, $data, $form_id, $prefix );
	}


	/**
	 * Addd a notch
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	public static function add_notch( $prefix, $form_id, $type, $data = true ){

		$type = sanitize_text_field( $type );
		// check repeats for type
		if( in_array( $type, array( 'loaded', 'view', 'engage' ) ) ){
			$prev = self::get_notch( $prefix, $form_id, 'partial' );
			if( !empty( $prev ) && !empty( $prev['datestamp'] ) ){
				// check its older than a day.
				if( substr( $prev['datestamp'],0,10) == date( 'Y-m-d' ) ){
					return; // no catch
				}
			}
		}

		return self::save( $type, $data, $form_id, $prefix );
	}

	/**
	 * Save tracking data
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Meta key
	 * @param mixed $value Value to save
	 * @param int|string $form_id ID of form
	 * @param string $prefix Form type prefix
	 * @param null|array $where Optional. Where clause array in format $wpdb->update() will accept. If is null, the default, new record will be created.
	 *
	 * @return int|false ID of row updated, or false on error.
	 */
	public static function save( $key, $value, $form_id, $prefix, $where = null ){
		global $wpdb;

		// check data is not array or whatnot
		if( is_array( $value) || is_object( $value ) ){
			if( empty( $value ) ){
				$value = null; // no empty arrays please.
			}else{
				$value = json_encode( $value );
			}
		}
		// setup the datablock
		$data = array(
			'form_id'	=>	$form_id,
			'prefix'	=>	$prefix,
			'user_id'	=>	get_current_user_id(),
			'user_key'	=>	self::get_user_key(),			
			'meta_key'	=>	$key,
			'meta_value'=>	$value,
		);
		// check if its insert or update
		if( empty( $where ) ){
			// add a datestamp
			$data['datestamp']	=	current_time( 'mysql', true );

			$wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );
			if( true == $wpdb->result ) {
				return $wpdb->insert_id;
			}

		}else{
			$wpdb->update( $wpdb->prefix . "formworks_tracker", $data, $where );
			if( true == $wpdb->result ) {
				return $wpdb->insert_id;
			}
		}

		return false;

	}

	/**
	 * Get a saved notch
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix Form type prefix
	 * @param int|string $form_id ID of form
	 * @param string $key Meta key
	 *
	 * @return array|null|object|void
	 */
	public static function get_notch( $prefix, $form_id, $key ){
		
		global $wpdb;

		$query = $wpdb->prepare("
			SELECT `id`, `meta_value`, `datestamp`
			FROM `{$wpdb->prefix}formworks_tracker` 
			WHERE 
			`form_id` = %s && 
			`prefix` = %s &&
			`user_id` = %d && 
			`user_key` = %s && 
			`meta_key` = %s;",
			$form_id, $prefix, get_current_user_id(), self::get_user_key(), $key );

		return $wpdb->get_row( $query, ARRAY_A );

	}

	/**
	 * Get a saved partial submission
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix Form type prefix
	 * @param int|string $form_id ID of form
	 * @param null|string $field Optional Name of field
	 *
	 * @return bool|false|int
	 */
	public static function add_partial( $prefix, $form_id, $field = null ){

		$data = array();
		$partial = self::get_notch( $prefix, $form_id, 'partial' );

		// is there a partial?
		if( !empty( $partial ) ){
			$pre_data = json_decode( $partial['meta_value'], ARRAY_A );
			if( !empty( $pre_data ) ){
				$data = $pre_data;
			}
		}
		// no blank data.
		if( null !== $field ){
			if( in_array( $field, $data ) ){
				$counts = array_count_values( $data );
				// field edit
				if( $counts[ $field ] >= 2 ){
					self::save( 'field_edit', $field, $form_id, $prefix  );
				}
			}else{
				self::save( 'field_engage', $field, $form_id, $prefix );
			}
			// merge with current data (if any)
			$data[] = $field;
		}

		// save partial
		return self::save( 'partial', $data, $form_id, $prefix, $partial );

	}


	/**
	 * Adds a submission notch and removes associated partial
	 *
	 * @since 1.0.0
	 *
	 * @param string $prefix Form type prefix
	 * @param int|string $form_id ID of form
	 *
	 * @return bool
	 */
	public static function add_submission( $prefix, $form_id ){
		// get the partial 
		$partial = self::get_notch( $prefix, $form_id, 'partial' );
		if( empty( $partial ) ){
			return self::save( 'submission', true, $form_id, $prefix );
		}
		// save with completion time
		$timespent = current_time( 'timestamp', true ) - strtotime( $partial['datestamp'] );
		if( self::save( 'submission', $timespent, $form_id, $prefix ) ){
			return self::kill_partial( $partial['id'] );
		}

		//fallback, all is not well.
		return false;
	}

	/**
	 * Delete a saved a partial
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Row ID
	 *
	 * @return int|false The number of rows updated, or false on error
	 */
	public static function kill_partial( $id ){
		global $wpdb;

		return $wpdb->delete( $wpdb->prefix . "formworks_tracker", array( 'id' => $id )  );

	}	

}
