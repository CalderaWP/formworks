<?php


add_filter( 'formworks_stat_modules', 'formworks_summary_story' );
function formworks_summary_story( $modules ){

	$modules['summary_story'] = array(
		'title' => __('Summary Story', 'firmworks'),
		'description' => __('Give the summary in a easy readable story.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_summary_story'
	);

	return $modules;

}

function formworks_get_summary_story( $data, $request ){
		global $wpdb;
		
		$stats = formworks_get_core_events( $data, $request );

		$total_views = array_sum( $stats['datasets'][ 'view' ][ 'data' ] );
		$total_loads = array_sum( $stats['datasets'][ 'loaded' ][ 'data' ] );
		$total_submitions = array_sum( $stats['datasets'][ 'submission' ][ 'data' ] );

		// stories
		$starts = array(
			'this_week' => __( 'This week', 'formworks' ),
			'this_month' => __( 'This month', 'formworks' ),
			'last_month' => __( 'Last month', 'formworks' ),
			'custom' => sprintf( __( 'Between %s and %s', 'formworks' ), '<strong>'.$request['filters']['date']['start'].'</strong>', '<strong>'.$request['filters']['date']['end'].'</strong>'),
		);		

		$stats['conversion_story'] = '<p>' . $starts[ $request['filters']['date']['preset'] ] . ', ';
		
		$stats['conversion_story'] .= sprintf( _n( 'The form was loaded %s time ', 'The form was loaded %s times ', $total_loads, 'formworks' ), '<strong>'. $total_loads . '</strong>');		
		$stats['conversion_story'] .= sprintf( _n( 'and received %s submission. ', 'and received %s submissions. ', $total_submitions, 'formworks' ), '<strong>'. $total_submitions . '</strong>' );		
		$stats['conversion_story'] .= '</p>';
		$stats['conversion_story'] .= '<p>';
		if( !empty( $stats['datasets'][ 'engage_conversion' ]['rate'] ) ){
			if( $stats['datasets'][ 'engage_conversion' ]['rate'] > 0 ){
				$stats['conversion_story'] .= sprintf( __( '%s of visitors actively engage the form and %s of engaged users go on to a submission. ', 'formworks' ), 
					'<strong>' . $stats['datasets'][ 'engage_load' ]['rate'] . '%</strong>',
					'<strong>' . $stats['datasets'][ 'engage_conversion' ]['rate'] . '%</strong>'
				 );			
				$stats['conversion_story'] .= '<br>';
			}
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
			if( !empty( $stats['datasets'][ 'view_conversion' ] ) ){
				$stats['conversion_story'] .= sprintf( __( 'Of those that do see the form, the conversion rate is %s. ', 'formworks' ), '<strong>'.$stats['datasets'][ 'view_conversion' ]['rate'] . '%</strong>' );			
			}
			if( !empty( $stats['datasets'][ 'engage_conversion' ] ) ){

				if( $stats['datasets'][ 'engage_conversion' ]['rate'] > 0 ){
					$stats['conversion_story'] .= sprintf( __( 'This means that %s of visitors who see the form, actively engage it.', 'formworks' ), 
						'<strong>' . $stats['datasets'][ 'engage_view' ]['rate'] . '%</strong>'
					 );			
				}
			}
			$stats['conversion_story'] .= '</p>';

		}else{
			$stats['conversion_story'] .= '<p>';
			$stats['conversion_story'] .= __( 'Form loads and views are the same. This indicates that the form is visible on page load.', 'formworks' );
			$stats['conversion_story'] .= '</p>';
		}

		return $stats['conversion_story'];
}