<?php


add_filter( 'formworks_stat_modules', 'formworks_field_drop_off' );
function formworks_field_drop_off( $modules ){

	$modules['field_drop_off'] = array(
		'title' =>  __('Field Abandonment', 'Formworks'),
		'description' => __('Last field engaged before abandoning the form.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_field_drop_off'
	);

	return $modules;

}

function formworks_get_field_drop_off( $data, $request ){
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
		`{$wpdb->prefix}formworks_tracker`.`meta_value` AS `data`
		FROM 
		`{$wpdb->prefix}formworks_tracker` 
		LEFT JOIN `{$wpdb->prefix}formworks_tracker` AS `device` ON ( `{$wpdb->prefix}formworks_tracker`.`user_key` = `device`.`user_key` && `device`.`meta_key` = 'device' )
		WHERE 
		{$device_filter}
		`{$wpdb->prefix}formworks_tracker`.`user_id` NOT IN (" . implode(',', $request['filters']['admins'] ) ." ) &&
		`{$wpdb->prefix}formworks_tracker`.`meta_key` = 'partial' &&
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
			$fields = array();
			foreach( $results as $result ){
				if( empty( $result['data'] ) ){
					continue;
				}
				
				$pre_data = json_decode( $result['data'], ARRAY_A );

				$field = array_pop( $pre_data );
				if( !isset( $fields[ $field ] ) ){
					$fields[ $field ] = 1;
				}else{
					$fields[ $field ] += 1;
				}
			}
			ksort( $fields );


			foreach( $fields as $field=>$value ){

				$data['values'][] = array(
					'label' => apply_filters( 'formworks_stats_field_name', $field, $request['prefix'], $request['form'] ),
					'data' => $value
				);

			}
		}

	return $data;
}