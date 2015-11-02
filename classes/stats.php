<?php
/**
 * Formworks stats generator.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

namespace calderawp\frmwks;

/**
 * Stats class.
 *
 * @package Formworks
 * @author  David Cramer
 */
class stats {

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

		$form_id = explode('_', $form_id, 2 );
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
		`prefix` = %s &&
		`form_id` = %s && 
		`meta_value` != ''
		GROUP BY `meta_key`
		;", $form_id[0], $form_id[1] );
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

		$form_parts = explode('_', $form_id, 2 );
		$form_id = $form_parts[1];
		$form_prefix = $form_parts[0];


		// add genereic data filter
		add_filter( 'formworks_populate_dataset', array( '\calderawp\frmwks\stats', 'process_dataset' ), 10, 3 );

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
			"partial" => array(
				"label"	=>	__('Abandoned', 'formworks' ),
				"lines" => array( "show" => true, "fill" => false, "lineWidth" => 1.4 ),
				"color" => "rgba(255, 100, 154, 1)",
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

		$device_filter = null;
		if( !empty( $args['filters'] ) ){

			if( !empty( $args['filters']['device'] ) ){
				$device_filter = "`device`.`meta_value` IN ( '" . implode("','", array_keys( (array) $args['filters']['device'] ) ) . "' ) &&";
			}	

		}
		

		$meta_keys = implode("','" , array_keys( $types ) );
		$query = $wpdb->prepare("
		SELECT 
		`{$wpdb->prefix}formworks_tracker`.`meta_key`, 
		COUNT( DISTINCT( `{$wpdb->prefix}formworks_tracker`.`user_key` ) ) AS `users`, 
		COUNT( DISTINCT( `{$wpdb->prefix}formworks_tracker`.`user_id` ) ) AS `logged`, 
		COUNT( `{$wpdb->prefix}formworks_tracker`.`meta_value` ) AS `total`,
		SUM( `{$wpdb->prefix}formworks_tracker`.`meta_value` ) AS `sum_total`,
		SUBSTR( `{$wpdb->prefix}formworks_tracker`.`datestamp`,1,{$sub_str}) AS `date`,
		`{$wpdb->prefix}formworks_tracker`.`datestamp`
		FROM 
		`{$wpdb->prefix}formworks_tracker` 
		LEFT JOIN `{$wpdb->prefix}formworks_tracker` AS `device` ON ( `{$wpdb->prefix}formworks_tracker`.`user_key` = `device`.`user_key` && `device`.`meta_key` = 'device' )
		WHERE
		{$device_filter}
		`{$wpdb->prefix}formworks_tracker`.`meta_key` IN ( '{$meta_keys}' ) &&
		`{$wpdb->prefix}formworks_tracker`.`form_id` = %s && 
		`{$wpdb->prefix}formworks_tracker`.`prefix` = %s && 
		`{$wpdb->prefix}formworks_tracker`.`meta_value` != '' && 
		`{$wpdb->prefix}formworks_tracker`.`datestamp` >= %s &&
		`{$wpdb->prefix}formworks_tracker`.`datestamp` <= %s 
		GROUP BY `{$wpdb->prefix}formworks_tracker`.`meta_key`, SUBSTR( `{$wpdb->prefix}formworks_tracker`.`datestamp`,1,{$sub_str})
		ORDER BY `{$wpdb->prefix}formworks_tracker`.`datestamp` ASC
		;", $form_id, $form_prefix, $start_str, $end_str );
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

		add_filter( 'formworks_stats_data', array( '\calderawp\frmwks\stats', 'conversion_story' ), 10, 3 );

		$stats['form_id'] = $form_id;
		$stats['form_prefix'] = $form_prefix;
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

		global $wpdb;
		
		
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
		$stats['conversion_story'] .= sprintf( _n( 'and received %s submission. ', 'and received %s submissions. ', $total_submitions, 'formworks' ), '<strong>'. $total_submitions . '</strong>' );		
		$stats['conversion_story'] .= '</p>';
		$stats['conversion_story'] .= '<p>';
		if( $stats['datasets'][ 'engage_conversion' ]['rate'] > 0 ){
			$stats['conversion_story'] .= sprintf( __( '%s of visitors actively engage the form and %s of engaged users go on to a submission. ', 'formworks' ), 
				'<strong>' . $stats['datasets'][ 'engage_load' ]['rate'] . '%</strong>',
				'<strong>' . $stats['datasets'][ 'engage_conversion' ]['rate'] . '%</strong>'
			 );			
			$stats['conversion_story'] .= '<br>';
		}
		if( !empty( $stats['datasets'][ 'load_conversion' ]['rate'] ) ){
			$stats['conversion_story'] .= sprintf( __( 'That is a conversion rate of %s from load', 'formworks' ), '<strong>' . $stats['datasets'][ 'load_conversion' ]['rate'] . '%</strong>' );

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
			$stats['conversion_story'] .= __( 'There are, however, more loads than views. This indicates that the form is not visible on page load.', 'formworks' );
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
		/*$colors = array(
			'#183b95',
			'#189932',
			'#8e6015',
			'#1c83d8',
			'#f2381f',
			'#778200',
		);*/
		
		
		$pie_config = array(
			'series' => array(
			    'pie' => array(
					'show'		=> true,
			    )
			),
			'legend' => array(
			    'show' => false
			)
		);

		$device_filter = null;
		if( !empty( $stats['config']['filters'] ) ){

			if( !empty( $stats['config']['filters']['device'] ) ){
				$device_filter = "`device`.`meta_value` IN ( '" . implode("','", array_keys( (array) $stats['config']['filters']['device'] ) ) . "' ) &&";
			}	

		}
		// get field editing
		$sub_str = strlen( $stats['start'] );
		$query = $wpdb->prepare("
		SELECT 
		`{$wpdb->prefix}formworks_tracker`.`meta_key`, 
		`{$wpdb->prefix}formworks_tracker`.`meta_value` AS `field`, 
		COUNT( DISTINCT( `{$wpdb->prefix}formworks_tracker`.`user_key` ) ) AS `users`, 
		COUNT( DISTINCT( `{$wpdb->prefix}formworks_tracker`.`user_id` ) ) AS `logged`, 
		COUNT( `{$wpdb->prefix}formworks_tracker`.`meta_value` ) AS `total`,
		SUBSTR( `{$wpdb->prefix}formworks_tracker`.`datestamp`,1,{$sub_str}) AS `date`,
		`{$wpdb->prefix}formworks_tracker`.`datestamp`
		FROM 
		`{$wpdb->prefix}formworks_tracker` 
		LEFT JOIN `{$wpdb->prefix}formworks_tracker` AS `device` ON ( `{$wpdb->prefix}formworks_tracker`.`user_key` = `device`.`user_key` && `device`.`meta_key` = 'device' )
		WHERE 
		{$device_filter}
		`{$wpdb->prefix}formworks_tracker`.`meta_key` = 'field_edit' &&
		`{$wpdb->prefix}formworks_tracker`.`form_id` = %s && 
		`{$wpdb->prefix}formworks_tracker`.`meta_value` != '' && 
		`{$wpdb->prefix}formworks_tracker`.`datestamp` >= %s &&
		`{$wpdb->prefix}formworks_tracker`.`datestamp` <= %s 
		GROUP BY `{$wpdb->prefix}formworks_tracker`.`meta_value`, SUBSTR( `{$wpdb->prefix}formworks_tracker`.`datestamp`,1,{$sub_str})
		ORDER BY `{$wpdb->prefix}formworks_tracker`.`datestamp` ASC
		;", $stats['form_id'], $stats['start'], $stats['end'] );
		$results = $wpdb->get_results( $query, ARRAY_A );

		// get field names 
		//$fields = 
		$stats['pies'] = array();
		if( !empty( $results ) ){
			$stats['pies']['edited'] = array();
			$stats['pies']['edited']['name'] = __('Field Edits', 'Formworks');
			$stats['pies']['edited']['description'] = __('Fields that where changed or edited before submission.');
			$stats['pies']['edited']['config'] = $pie_config;

			foreach( $results as $result ){
				$stats['pies']['edited']['data'][] = array(
					'label' => apply_filters( 'formworks_stats_field_name', $result['field'], $stats['form_prefix'], $stats['form_id'] ),
					'data' => $result['total']
				);		
			}
		}


		$sub_str = strlen( $stats['start'] );
		$query = $wpdb->prepare("
		SELECT 
		`{$wpdb->prefix}formworks_tracker`.`meta_key`, 
		`{$wpdb->prefix}formworks_tracker`.`meta_value` AS `data`

		FROM 
		`{$wpdb->prefix}formworks_tracker` 
		LEFT JOIN `{$wpdb->prefix}formworks_tracker` AS `device` ON ( `{$wpdb->prefix}formworks_tracker`.`user_key` = `device`.`user_key` && `device`.`meta_key` = 'device' )
		WHERE 
		{$device_filter}
		`{$wpdb->prefix}formworks_tracker`.`meta_key` = 'partial' &&
		`{$wpdb->prefix}formworks_tracker`.`form_id` = %s && 
		`{$wpdb->prefix}formworks_tracker`.`meta_value` != '' && 
		`{$wpdb->prefix}formworks_tracker`.`datestamp` >= %s &&
		`{$wpdb->prefix}formworks_tracker`.`datestamp` <= %s 
		GROUP BY `{$wpdb->prefix}formworks_tracker`.`meta_value`, SUBSTR( `{$wpdb->prefix}formworks_tracker`.`datestamp`,1,{$sub_str})
		ORDER BY `{$wpdb->prefix}formworks_tracker`.`datestamp` ASC
		;", $stats['form_id'], $stats['start'], $stats['end'] );
		$results = $wpdb->get_results( $query, ARRAY_A );

		if( !empty( $results ) ){
			$fields = array();
			foreach( $results as $result ){
				if( empty( $result['data'] ) ){
					continue;
				}
				
				$data = json_decode( $result['data'], ARRAY_A );

				$field = array_pop( $data );
				if( !isset( $fields[ $field ] ) ){
					$fields[ $field ] = 1;
				}else{
					$fields[ $field ] += 1;
				}
			}
			ksort( $fields );


			$stats['pies']['abandon_fields'] = array();
			$stats['pies']['abandon_fields']['name'] = __('Field Abandonment', 'Formworks');
			$stats['pies']['abandon_fields']['description'] = __('Last field engaged before abandoning the form.');
			$stats['pies']['abandon_fields']['config'] = $pie_config;

			foreach( $fields as $field=>$value ){

				$stats['pies']['abandon_fields']['data'][] = array(
					'label' => apply_filters( 'formworks_stats_field_name', $field, $stats['form_prefix'], $stats['form_id'] ),
					'data' => $value
				);

			}
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
						if( !empty( $datasets[ 'submission' ][ 'data' ][ $key ] ) && !empty( $datasets[ $dataset['is_conversion'] ][ 'data' ][ $key ] ) ){
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
						if( !empty( $datasets[ 'engage' ][ 'data' ][ $key ] ) && !empty(  $datasets[ $dataset['is_engage'] ][ 'data' ][ $key ] ) ){
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
