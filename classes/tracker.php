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
	 * retireive the user_key
	 *
	 * @since 1.0.0
	 *
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
	 * add a notch
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 *
	 * @return void
	 */
	public static function add_notch( $form_id, $type, $data = true ){

		$type = sanitize_text_field( $type );
		// check repeats for type
		if( in_array( $type, array( 'loaded', 'view', 'engage' ) ) ){
			$prev = self::get_notch( $form_id, 'partial' );
			if( !empty( $prev ) && !empty( $prev['datestamp'] ) ){
				// check its older than a day.
				if( substr( $prev['datestamp'],0,10) == date( 'Y-m-d' ) ){
					return; // no catch
				}
			}
		}
		return self::save( $type, $data, $form_id );
	}

	/**
	 * save data
	 *
	 * @since 1.0.0
	 *
	 * @param array $data to be saved
	 *
	 * @return bool true on success false on fail
	 */
	public static function save( $key, $value, $form_id, $where = null ){
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
			'user_id'	=>	get_current_user_id(),
			'user_key'	=>	self::get_user_key(),			
			'meta_key'	=>	$key,
			'meta_value'=>	$value,
		);
		// check if its insert or update
		if( empty( $where ) ){
			// add a datestamp
			$data['datestamp']	=	current_time( 'mysql', true );

			return $wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );
		}else{
			return $wpdb->update( $wpdb->prefix . "formworks_tracker", $data, $where );
		}

	}
	/**
	 * get a notch
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 * @param string $key notch type key
	 *
	 * @return void
	 */
	public static function get_notch( $form_id, $key ){
		
		global $wpdb;

		$query = $wpdb->prepare("
			SELECT `id`, `meta_value`, `datestamp`
			FROM `{$wpdb->prefix}formworks_tracker` 
			WHERE 
			`form_id` = %s && 
			`user_id` = %d && 
			`user_key` = %s && 
			`meta_key` = %s;",
			$form_id, get_current_user_id(), self::get_user_key(), $key );

		return $wpdb->get_row( $query, ARRAY_A );

	}	

	/**
	 * add a partial
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 * @param string $field ID of field partial
	 * @param string $value of partial
	 *
	 * @return void
	 */
	public static function add_partial( $form_id, $field = null, $value = null ){

		$data = array();
		$partial = self::get_notch( $form_id, 'partial' );
		// is there a partial?
		if( !empty( $partial ) ){
			$pre_data = json_decode( $partial['meta_value'], ARRAY_A );
			if( !empty( $pre_data ) ){
				$data = $pre_data;
			}

		}
		// no blank data.
		if( null !== $field && null !== $value ){
			// convert to array
			parse_str( $field . '=' . $value, $new_data );
			// merge with current data (if any)
			$data = array_merge( (array) $data, $new_data );
		}
		// save partial
		return self::save( 'partial', $data, $form_id, $partial );
	}


	/**
	 * Adds a submission notch and removes associated partial
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 *
	 */
	public static function add_submission( $form_id ){
		// get the partial 
		$partial = self::get_notch( $form_id, 'partial' );
		if( empty( $partial ) ){
			return self::save( 'submission', true, $form_id );
		}
		// save with completion time
		$timespent = current_time( 'timestamp', true ) - strtotime( $partial['datestamp'] );
		
		if( self::save( 'submission', $timespent, $form_id ) ){
			return self::kill_partial( $partial['id'] );
		}
		//fallback, all is not well.
		return false;
	}

	/**
	 * Kill a partial
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 *
	 */
	public static function kill_partial( $id ){
		global $wpdb;

		return $wpdb->delete( $wpdb->prefix . "formworks_tracker", array( 'id' => $id )  );

	}	

	/**
	 * get summary quick stats
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 *
	 * @return array quick stat array
	 */
	public static function get_quick_stats( $form_id ){
		global $wpdb;

		
		// set the types for the quick stats
		$types = array(
			"submission" => __('Submissions', 'formworks' ),
			"loaded" => __('Loads', 'formworks' ),
			"view" => __('Views', 'formworks' ),
			"engage" => __('Engagements', 'formworks' ),
			//"partial" => __('Incomplete', 'formworks' ),
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
		`form_id` = %s && 
		`meta_value` != ''
		GROUP BY `meta_key`
		;", $form_id );
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

	/**
	 * get summary quick stats
	 *
	 * @since 1.0.0
	 *
	 * @param string $form form ID
	 *
	 * @return array quick stat array
	 */
	public static function get_main_stats( $form_id, $args = array(), $filter = 'this_week' ){
		global $wpdb;

		// add genereic data filter
		add_filter( 'formworks_populate_dataset', array( '\calderawp\frmwks\tracker', 'process_dataset' ), 10, 3 );

		$defaults = array(
			'start' => 'last Monday',
			'end' => 'next Friday'
		);
		
		$config = array_merge( $defaults, $args );

		$lables = array();
		$datasets = array(
			"submission" => array(
				"label"	=>	__('Submissions', 'formworks' ),
				"lines" => array( "show" => true, "fill" => false, "lineWidth" => 1.4 ),
				"color" => "rgba(219, 68, 55,1)",
				"shadowSize" => 0,
				"points" => array(
					"show" => true,
					"radius" => 3,
					"lineWidth" => 1,
        			"symbol" => "circle"
				)
			),
			"loaded" => array(
				"label"	=>	__('Loads', 'formworks' ),
				"lines" => array( "show" => true, "fill" => false, "lineWidth" => 1.4 ),
				"color" => "rgba(128, 191, 64, 1)",
				"shadowSize" => 0,
				"points" => array(
					"show" => true,
					"radius" => 3,
					"lineWidth" => 1,
        			"symbol" => "circle"
				)
			),
			"view" => array(
				"label"	=>	__('Views', 'formworks' ),
				"lines" => array( "show" => true, "fill" => false, "lineWidth" => 1.4 ),
				"color" => "rgba(130,130,130, 1)",
				"shadowSize" => 0,
				"points" => array(
					"show" => true,
					"radius" => 3,
					"lineWidth" => 1,
        			"symbol" => "circle"
				)
			),
			"engage" => array(
				"label"	=>	__('Engagements', 'formworks' ),
				"lines" => array( "show" => true, "fill" => false, "lineWidth" => 1.4 ),
				"color" => "rgba(245, 181, 4, 1)",
				"shadowSize" => 0,
				"points" => array(
					"show" => true,
					"radius" => 3,
					"lineWidth" => 1,
        			"symbol" => "circle"
				)
			),
			"load_conversion" => array(
				"label"	=>	__('From Load', 'formworks' ),
				"description" => __('Percentage of visitors that complete the form after it loads.', 'formworks' ),
				"hide"	=>	true,
				"is_conversion"	=>	'loaded',
			),
			"view_conversion" => array(
				"label"	=>	__('From View', 'formworks' ),
				"description" => __('Percentage of visitors that complete the form after viewing it.', 'formworks' ),
				"hide"	=>	true,
				"is_conversion"	=>	'view',
			),
			"engage_conversion" => array(
				"label"	=>	__('From Engagement', 'formworks' ),
				"description" => __('Percentage of visitors that actively engaged and completed it.', 'formworks' ),
				"hide"	=>	true,
				"is_conversion"	=>	'engage',
			),
			"engage_view" => array(
				"label"	=>	__('Engage View', 'formworks' ),
				"description" => __('Percentage of visitors that actively engage the form when viewed.', 'formworks' ),
				"hide"	=>	true,
				"is_engage"	=>	'view',
			),
			"engage_load" => array(
				"label"	=>	__('Engage Load', 'formworks' ),
				"description" => __('Percentage of visitors that actively engage the form once loaded.', 'formworks' ),
				"hide"	=>	true,
				"is_engage"	=>	'loaded',
			),

		);
	
		/**
		 * Filter datasets to allow adding or removing
		 *
		 * @param array $datasets config
		 */
		$datasets = apply_filters( 'formworks_datasets', $datasets );

		// set the types for the quick stats
		foreach( $datasets as $dataset=>$set_config ){
			$types[ $dataset ] = $set_config['label'];
		}


		$dataset_base = array();

		$start = strtotime( $config['start'] );
		$start_str = date( 'Y-m-d', $start );
		$end = strtotime( $config['end'] );
		$end_str = date( 'Y-m-d', $end );

		$date_start = new \DateTime( $start_str );
		$date_end = new \DateTime( $end_str );
		$date_diff = date_diff($date_start, $date_end);
		$label_format = 'D, M j';
		$date_format = 'Y-m-d';
		$sub_str = 10;
		$runtime = $date_diff->days;
		$interval = DAY_IN_SECONDS;
		if( $date_diff->days > 70 ){
			$label_format = 'M';
			$date_format = 'Y-m';
			$sub_str = 7;
			$runtime = $date_diff->days / 30;
			$interval = MONTH_IN_SECONDS;
		}
		for( $d = 0; $d <= $runtime; $d++){
			$current = $start + ( $interval * $d );
			$labels[] = date( $label_format, $current );
			$date_str = date( $date_format, $current );
			$dataset_base[ $date_str ] = date( $label_format, $current );
			foreach( $datasets as $type=>$setting ){
				$datasets[ $type ]['data'][ date( $label_format, $current ) ] = 0;
			}			

		}

		$stats = array(
			"labels"	=>	$labels,
		);



		$meta_keys = implode("','" , array_keys( $types ) );
		$query = $wpdb->prepare("
		SELECT 
		`meta_key`, 
		COUNT( DISTINCT( `user_key` ) ) AS `users`, 
		COUNT( DISTINCT( `user_id` ) ) AS `logged`, 
		COUNT( `meta_value` ) AS `total`,
		SUM( `meta_value` ) AS `sum_total`,
		SUBSTR( `datestamp`,1,{$sub_str}) AS `date`,
		`datestamp`
		FROM 
		`{$wpdb->prefix}formworks_tracker` 
		WHERE 
		`meta_key` IN ( '{$meta_keys}' ) &&
		`form_id` = %s && 
		`meta_value` != '' && 
		`datestamp` >= %s &&
		`datestamp` <= %s 
		GROUP BY `meta_key`, SUBSTR( `datestamp`,1,{$sub_str})
		ORDER BY `datestamp` ASC
		;", $form_id, $start_str, $end_str );
		$results = $wpdb->get_results( $query, ARRAY_A );

		foreach( $results as $result ){

			$datasets[ $result['meta_key'] ]['data'][ $dataset_base[ $result['date'] ] ] = (int) $result['total'];
			$datasets[ $result['meta_key'] ]['metakey'] = $result['meta_key'];

		}

		// filter data results
		foreach( $datasets as $dataset_key => $dataset ){
			
			$datasets[ $dataset_key ] = apply_filters( 'formworks_populate_dataset', $dataset, $dataset_key, $datasets );
			$datasets[ $dataset_key ] = apply_filters( 'formworks_populate_dataset-' . $dataset_key, $datasets[ $dataset_key ], $datasets );

		}

		add_filter( 'formworks_stats_data', array( '\calderawp\frmwks\tracker', 'conversion_story' ), 10, 3 );

		$stats['config'] = $config;
		$stats['start'] = $start_str;
		$stats['end'] = $end_str;
		$stats['datasets'] = $datasets;
		$options = array(
			"xaxis" => array(
				"mode"			=> "categories",
				"tickLength"	=> 0,
			),
			"legend" => array(
				"show" => false,
			),
			"grid" => array(
				"hoverable" => true,
				"clickable" => true,
				"show" => true,
				"borderWidth" => 1,
				"borderColor" => array(
					"top"		=>	"#ccc",
					"right"		=>	"#ccc",
					"bottom"	=>	"#ccc",
					"left"		=>	"#ccc",
				),
				"markings" => array(),
			),
		);
		
		$stats['options'] = apply_filters( 'formworks_chart_options', $options, $config, $filter );
		
		$stats = apply_filters( 'formworks_stats_data', $stats, $config, $filter );
		
		return $stats;

	}


	/**
	 * add conversion story to stats object
	 *
	 * @since 1.0.0
	 *
	 *
	 * @return array stats object stufs
	 */
	static public function conversion_story( $stats, $config, $filter ){

		
		
		$total_views = array_sum( $stats['datasets'][ 'view' ][ 'data' ] );
		$total_loads = array_sum( $stats['datasets'][ 'loaded' ][ 'data' ] );
		$total_submitions = array_sum( $stats['datasets'][ 'submission' ][ 'data' ] );

		// stories
		$starts = array(
			'this_week' => __( 'This week', 'formworks' ),
			'this_month' => __( 'This month', 'formworks' ),
			'last_month' => __( 'Last month', 'formworks' ),
			'custom' => sprintf( __( 'Between %s and %s', 'formworks' ), '<strong>'.$stats['start'].'</strong>', '<strong>'.$stats['end'].'</strong>'),
		);

		$stats['conversion_story'] = '<p>' . $starts[ $filter ] . ', ';
		
		$stats['conversion_story'] .= sprintf( _n( 'The form was loaded %s time ', 'The form was loaded %s times ', $total_loads, 'formworks' ), '<strong>'. $total_loads . '</strong>');		
		$stats['conversion_story'] .= sprintf( _n( 'and recieved %s submission. ', 'and recieved %s submissions. ', $total_submitions, 'formworks' ), '<strong>'. $total_submitions . '</strong>' );		
		$stats['conversion_story'] .= '</p>';
		$stats['conversion_story'] .= '<p>';
		if( $stats['datasets'][ 'engage_conversion' ]['rate'] > 0 ){
			$stats['conversion_story'] .= sprintf( __( '%s of visitors actively engage the form and %s of engaging users go on to a submission. ', 'formworks' ), 
				'<strong>' . $stats['datasets'][ 'engage_load' ]['rate'] . '%</strong>',
				'<strong>' . $stats['datasets'][ 'engage_conversion' ]['rate'] . '%</strong>'
			 );			
			$stats['conversion_story'] .= '<br>';
		}
		if( !empty( $stats['datasets'][ 'load_conversion' ]['rate'] ) ){
			$stats['conversion_story'] .= sprintf( __( 'Thats a conversion rate of %s from load', 'formworks' ), '<strong>' . $stats['datasets'][ 'load_conversion' ]['rate'] . '%</strong>' );

			if( $stats['datasets'][ 'engage_conversion' ]['rate'] > 0 ){
				$stats['conversion_story'] .= ' ';
				$stats['conversion_story'] .= sprintf( __( 'and leaves a %s abandon rate.', 'formworks' ), 
					'<strong>' . ( 100 - $stats['datasets'][ 'engage_conversion' ]['rate'] ) . '%</strong>'
				 );		
			}else{
				$stats['conversion_story'] .='.';
			}
		}

		$stats['conversion_story'] .= '</p>';
		// is hidden?
		if( $total_views < $total_loads ){
			$view_percent = round( 100 - ( ( $total_views / $total_loads ) * 100 ), 1 );
			$stats['conversion_story'] .= '<p>';
			$stats['conversion_story'] .= __( 'There are, However, more loads than views. This indicates that the form is not visible on page load.', 'formworks' );
			$stats['conversion_story'] .= sprintf( __( "Therefore %s of page visitors don't even see the form. ", 'formworks' ), '<strong>' . $view_percent . '%</strong>' );
			$stats['conversion_story'] .= '</p>';

			$stats['conversion_story'] .= '<p>';
			$stats['conversion_story'] .= sprintf( __( 'Of those that do see the form, the conversion rate is %s. ', 'formworks' ), '<strong>'.$stats['datasets'][ 'view_conversion' ]['rate'] . '%</strong>' );			

			if( $stats['datasets'][ 'engage_conversion' ]['rate'] > 0 ){
				$stats['conversion_story'] .= sprintf( __( 'This means that %s of visitors who see the form, actively engage it.', 'formworks' ), 
					'<strong>' . $stats['datasets'][ 'engage_view' ]['rate'] . '%</strong>'
				 );			
			}

			$stats['conversion_story'] .= '</p>';

		}else{
			$stats['conversion_story'] .= '<p>';
			$stats['conversion_story'] .= __( 'Form loads and views are the same. This indicates that the form is visible on page load.', 'formworks' );
			$stats['conversion_story'] .= '</p>';
		}


		// fun fun! add posts
		$index = 0;
		global $wpdb;
		foreach( $stats['datasets'][ 'view' ]['data'] as $date=>$val ){
			// harsh!
			$thisdate = gmdate('Y-m-d', strtotime( $date ) );
			$posts = $wpdb->get_results( "SELECT `post_title` FROM `" . $wpdb->posts ."` WHERE `post_type` = 'post' && `post_status` = 'publish' && `post_date_gmt` >= '" . $thisdate ." 00:00:00' && `post_date_gmt` <= '" . $thisdate ." 23:59:59' LIMIT 1;", ARRAY_A );
			if( empty( $posts ) ){
				$index++;
				continue;
			}

			foreach( $posts as $event_post ){
				$stats['options']['grid']['markings'][] = array(
					"label" => $event_post['post_title'],
					"color"	=>	"rgba(0,0,0,0.1)",
					"lineWidth" => 1,
					"xaxis" => array( "from" => $index, "to" => $index ),
				);
			}

			$index++;			
		}



		return $stats;
	}

	/**
	 * preprocess datasets
	 *
	 * @since 1.0.0
	 *
	 *
	 * @return array dataset stufs
	 */
	static public function process_dataset( $dataset, $type, $datasets ){

		if( !empty( $dataset['is_conversion'] ) ){
			
			if( !empty( $datasets[ 'submission' ][ 'data' ] ) ){
				if( !empty( $datasets[ $dataset['is_conversion'] ][ 'data' ] ) ){
					foreach ($dataset['data'] as $key => $value) {
						if( !empty( $datasets[ 'submission' ][ 'data' ][ $key ] ) ){
							$dataset['data'][ $key ] = $datasets[ 'submission' ][ 'data' ][ $key ] / $datasets[ $dataset['is_conversion'] ][ 'data' ][ $key ] * 100;
						}
					}
					$submission_total = array_sum( $datasets[ 'submission' ]['data'] );
					$this_total = array_sum( $dataset['data'] );
					if( $submission_total > 0 && $this_total > 0 ){
						$dataset['rate'] = round( $this_total / $submission_total , 1 );
					}else{
						$dataset['rate'] = 0;
					}
				}
			}
		}
		if( !empty( $dataset['is_engage'] ) ){
			
			if( !empty( $datasets[ 'engage' ][ 'data' ] ) ){
				if( !empty( $datasets[ $dataset['is_engage'] ][ 'data' ] ) ){
					foreach ($dataset['data'] as $key => $value) {
						if( !empty( $datasets[ 'engage' ][ 'data' ][ $key ] ) ){
							$dataset['data'][ $key ] = $datasets[ 'engage' ][ 'data' ][ $key ] / $datasets[ $dataset['is_engage'] ][ 'data' ][ $key ] * 100;
						}
					}
					$submission_total = array_sum( $datasets[ 'submission' ]['data'] );
					$this_total = array_sum( $dataset['data'] );
					if( $submission_total > 0 && $this_total > 0 ){					
						$dataset['rate'] = round( $this_total / $submission_total , 1 );
					}else{
						$dataset['rate'] = 0;
					}
				}
			}
		}
		return $dataset;
	}
}
