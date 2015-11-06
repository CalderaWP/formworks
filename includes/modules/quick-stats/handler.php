<?php


add_filter( 'formworks_stat_modules', 'formworks_quick_stats' );
function formworks_quick_stats( $modules ){

	$modules['quick_stats'] = array(
		'title' => __('Quick Stats', 'firmworks'),
		'description' => __('Give a global overview for the form.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_quick_stats'
	);

	return $modules;

}

function formworks_get_quick_stats( $data, $request ){
		global $wpdb;
		$form_id = $request['form'];
		// set the types for the quick stats
		$types = array(
			"submission" => __('Submissions', 'formworks' ),
			"loaded" => __('Loads', 'formworks' ),
			"view" => __('Views', 'formworks' ),
			"engage" => __('Engagements', 'formworks' ),
		);

		$meta_keys = implode("','" , array_keys( $types ) );
		$query = $wpdb->prepare("
		SELECT 
		`meta_key`, 
		COUNT( DISTINCT( `user_key` ) ) AS `users`, 
		COUNT( DISTINCT( `user_id` ) ) AS `logged`, 
		COUNT( `meta_value` ) AS `total`,
		SUM( `meta_value` ) AS `sum_total`,
		`datestamp`
		FROM 
		`{$wpdb->prefix}formworks_tracker` 
		WHERE 
		`meta_key` IN ( '{$meta_keys}' ) &&
		`prefix` = %s &&
		`form_id` = %s && 
		`meta_value` != ''
		GROUP BY `meta_key`
		;", $request['prefix'], $form_id );
		$results = $wpdb->get_results( $query, ARRAY_A );

		$quick_stats = array();
		$current_timestamp = current_time( 'timestamp', true );
		// resetup
		foreach( $types as $type_key => $type ){
			$quick_stats[ $type_key ] = array(
				'name' => $type,
				'users' => 0,
				'total' => 0,
				'sum_total' => 0
			);
		}
		foreach( $results as $result ){
			if( $result[ 'meta_key' ] == 'partial' ){
				// check time - ignore todays stuff
				if( strtotime( $result[ 'datestamp' ] ) > strtotime( '-24 hours' ) ){
					continue;
				}
			}
			$quick_stats[ $result[ 'meta_key' ] ]['users'] = $result['users'];
			$quick_stats[ $result[ 'meta_key' ] ]['total'] = $result['total'];
			$quick_stats[ $result[ 'meta_key' ] ]['sum_total'] = $result['sum_total'];
			$quick_stats[ $result[ 'meta_key' ] ]['n'] = _n( 'user', 'users', $result['users'], 'formworks' );
		}
		
		// get_conversions
		foreach( $types as $type_key => $type ){
			if( $type_key == "submission" ){
				$average = 0;
				if( $quick_stats[ "submission" ]['total'] > 0 ){
					$average = $quick_stats[ "submission" ]['sum_total'] / $quick_stats[ "submission" ]['total'];
				}
				$quick_stats[ $type_key ][ 'average_time' ] = human_time_diff( ( $current_timestamp - $average ), $current_timestamp );
				continue;
			}
			if( $type_key == "partial" ){
				if( !empty( $quick_stats[ $type_key ]['users'] ) ){
					$quick_stats[ $type_key ][ 'conversion' ] = round( $quick_stats[ $type_key ]['users'] / $quick_stats[ "engage" ]['total'] * 100, 1 ) . '%';
				}else{
					$quick_stats[ $type_key ][ 'conversion' ] = '0%';
				}
				unset( $quick_stats[ $type_key ]['total'] );
				$quick_stats[ $type_key ][ 'rate_type' ] = __('Abandonment', 'formworks');
				continue;
			}
			$quick_stats[ $type_key ][ 'rate_type' ] = __('Conversion', 'formworks');
			if( !empty( $quick_stats[ $type_key ]['total'] ) ){
				$quick_stats[ $type_key ][ 'conversion' ] = round( $quick_stats[ "submission" ]['total'] / $quick_stats[ $type_key ]['sum_total'] * 100, 1 ) . '%';
			}else{
				$quick_stats[ $type_key ][ 'conversion' ] = '0%';
			}
		}
		return $quick_stats;
	}