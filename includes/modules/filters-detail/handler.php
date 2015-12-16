<?php
add_filter( 'formworks_stat_modules', 'formworks_filters_detail' );

/**
 * Add the summary module
 *
 * @since 1.0.0
 *
 * @uses "formworks_stat_modules" filter
 *
 * @param array $modules
 *
 * @return array
 */
function formworks_filters_detail( $modules ){

	$modules['filters_detail'] = array(
		'title' => __('Filters details line', 'formworks'),
		'description' => __('Describes the current data being shown.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_filters_detail'
	);

	return $modules;

}

/**
 * Call back for filters module
 *
 * @since 1.0.0
 *
 * @param $data
 * @param $request
 *
 * @return string
 */
function formworks_get_filters_detail( $data, $request ){
		
		$devices = esc_html__('all devices', 'formworks');
		if( !empty( $request['filters']['device'] ) ){
			$list = array_map( function( $a ){ return ucfirst( $a ) . 's'; }, array_keys( $request['filters']['device'] ) );
			$last = array_pop( $list );
			$devices = implode( ', ', $list );
			if( !empty( $devices ) ){
				$devices .= ( count( $devices ) > 1 ? ', ' : ' ' ) . esc_html__( 'and', 'formworks') . ' ';				
			}else{
				$last .= ' ' . esc_html__('only', 'formworks');
			}
			$devices .= $last;
		}

		$from_date = date_i18n( get_option( 'date_format' ), strtotime( $request['filters']['date']['start'] ) );
		$to_date = date_i18n( get_option( 'date_format' ), strtotime( $request['filters']['date']['end'] ) );
		
		return esc_html__( 'Between', 'formworks') . ' ' . $from_date .' ' . esc_html__( 'and', 'formworks') .' ' . $to_date . ', from ' . $devices . '.';
}
