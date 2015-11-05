<?php


add_filter( 'formworks_stat_modules', 'formworks_core_events' );
function formworks_core_events( $modules ){

	$modules['core_events'] = array(
		'title' => __('Core Events', 'firmworks'),
		'description' => __('Chart the main events, load, view engage and submission.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_core_events'
	);

	return $modules;

}

function formworks_get_core_events( $data, $request ){
		global $wpdb;
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
			)

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

		$start = strtotime( $request['filters']['date']['start'] );
		$start_str = date( 'Y-m-d', $start );
		$end = strtotime( $request['filters']['date']['end'] );
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
		if( !empty( $request['filters'] ) ){

			if( !empty( $request['filters']['device'] ) ){
				$device_filter = "`device`.`meta_value` IN ( '" . implode("','", array_keys( (array) $request['filters']['device'] ) ) . "' ) &&";
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
		;", $request['form'], $request['prefix'], $start_str, $end_str );
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

		$stats['form_id'] = $request['form'];
		$stats['form_prefix'] = $request['prefix'];
		$stats['config'] = $request;
		$stats['start'] = $start_str;
		$stats['end'] = $end_str;
		$stats['datasets'] = $datasets;
		$options = array(
			"xaxis" => array(
				"mode"			=> "categories",
				"tickLength"	=> 0,
			),
			"legend" => array(
				"show" => true,
				"backgroundColor" => '#ffffff'
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
		

		// fun fun! add posts
		$index = 0;
		global $wpdb;
		foreach( $datasets[ 'view' ]['data'] as $date=>$val ){
			// harsh!
			$thisdate = gmdate('Y-m-d', strtotime( $date ) );
			$posts = $wpdb->get_results( "SELECT `post_title` FROM `" . $wpdb->posts ."` WHERE `post_type` = 'post' && `post_status` = 'publish' && `post_date_gmt` >= '" . $thisdate ." 00:00:00' && `post_date_gmt` <= '" . $thisdate ." 23:59:59' LIMIT 1;", ARRAY_A );
			if( empty( $posts ) ){
				$index++;
				continue;
			}

			foreach( $posts as $event_post ){
				$options['grid']['markings'][] = array(
					"label" => $event_post['post_title'],
					"color"	=>	"rgba(0,0,0,0.1)",
					"lineWidth" => 1,
					"xaxis" => array( "from" => $index, "to" => $index ),
				);
			}

			$index++;			
		}

		$stats['options'] = apply_filters( 'formworks_chart_options', $options, $request );
		
		$stats = apply_filters( 'formworks_stats_data', $stats, $request );
		
		return $stats;

}