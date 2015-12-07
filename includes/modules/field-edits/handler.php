<?php


add_filter( 'formworks_stat_modules', 'formworks_field_edits' );
function formworks_field_edits( $modules ){

	$modules['field_edits'] = array(
		'title' => __('Field Edits', 'firmworks'),
		'description' => __('Fields that where changed or edited before submission.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_field_edits'
	);

	return $modules;

}

function formworks_get_field_edits( $data, $request ){
		global $wpdb;


		$device_filter = null;
		if( !empty( $request['filters'] ) ){

			if( !empty( $request['filters']['device'] ) ){
				$device_filter = "`device`.`meta_value` IN ( '" . implode("','", array_keys( (array) $request['filters']['device'] ) ) . "' ) &&";
			}	

		}


		// get field editing
		$sub_str = strlen( $request['filters']['date']['start'] );
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
		`{$wpdb->prefix}formworks_tracker`.`user_id` NOT IN (" . implode(',', $request['filters']['admins'] ) ." ) &&
		`{$wpdb->prefix}formworks_tracker`.`meta_key` = 'field_edit' &&
		`{$wpdb->prefix}formworks_tracker`.`form_id` = %s && 
		`{$wpdb->prefix}formworks_tracker`.`prefix` = %s && 
		`{$wpdb->prefix}formworks_tracker`.`meta_value` != '' && 
		`{$wpdb->prefix}formworks_tracker`.`datestamp` >= %s &&
		`{$wpdb->prefix}formworks_tracker`.`datestamp` <= %s 
		GROUP BY `{$wpdb->prefix}formworks_tracker`.`meta_value`, SUBSTR( `{$wpdb->prefix}formworks_tracker`.`datestamp`,1,{$sub_str})
		ORDER BY `{$wpdb->prefix}formworks_tracker`.`datestamp` ASC
		;", $request['form'], $request['prefix'], $request['filters']['date']['start'], $request['filters']['date']['end'] );
		$results = $wpdb->get_results( $query, ARRAY_A );

		// get field names 
		//$fields = 
		$data = array(
			'values' => array(),
			'config' => array(
				'series' => array(
				    'pie' => array(
						'show'		=> true,
				    )
				),
				'legend' => array(
				    'show' => false
				)
			)
		);
		if( !empty( $results ) ){

			foreach( $results as $result ){
				$data['values'][] = array(
					'label' => apply_filters( 'formworks_stats_field_name', $result['field'], $request['prefix'], $request['form'] ),
					'data' => $result['total']
				);		
			}
		}

	return $data;
}