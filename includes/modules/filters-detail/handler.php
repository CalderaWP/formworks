<?php


add_filter( 'formworks_stat_modules', 'formworks_filters_detail' );
function formworks_filters_detail( $modules ){

	$modules['filters_detail'] = array(
		'title' => __('Filters details line', 'firmworks'),
		'description' => __('Shoes the selected filters as text.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_filters_detail'
	);

	return $modules;

}

function formworks_get_filters_detail( $data, $request ){
		
		$devices = __('all devices', 'formworks');
		if( !empty( $request['filters']['device'] ) ){
			$list = array_map( function( $a ){ return ucfirst( $a ) . 's'; }, array_keys( $request['filters']['device'] ) );
			$last = array_pop( $list );
			$devices = implode( ', ', $list );
			if( !empty( $devices ) ){
				$devices .= ( count( $devices ) > 1 ? ', ' : ' ' ) . __( 'and', 'formworks') . ' ';				
			}else{
				$last .= ' ' . __('only', 'formworks');
			}
			$devices .= $last;
		}

		$from_date = date_i18n( get_option( 'date_format' ), strtotime( $request['filters']['date']['start'] ) );
		$to_date = date_i18n( get_option( 'date_format' ), strtotime( $request['filters']['date']['end'] ) );
		
		return __( 'Between', 'formworks') . ' ' . $from_date .' ' . __( 'and', 'formworks') .' ' . $to_date . ', from ' . $devices . '.';
}