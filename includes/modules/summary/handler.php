<?php
add_filter( 'formworks_stat_modules', 'formworks_summary_story' );

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
function formworks_summary_story( $modules ){

	$modules['summary_story'] = array(
		'title' => __('Summary Story', 'formworks'),
		'description' => __('The summary in a easy readable story.', 'formworks'),
		'template' => dirname( __FILE__ ) . '/template.php',
		'handler' => 'formworks_get_summary_story'
	);

	return $modules;

}

/**
 * Callback to create a story
 *
 * @since 1.0.0
 *
 * @param array $data
 * @param array $request
 *
 * @return string
 */
function formworks_get_summary_story( $data, $request ){
	global $wpdb;

	$stats = formworks_get_core_events( $data, $request );

	$total_views = array_sum( $stats['datasets'][ 'view' ][ 'data' ] );
	$total_engage = array_sum( $stats['datasets'][ 'engage' ][ 'data' ] );
	$total_loads = array_sum( $stats['datasets'][ 'loaded' ][ 'data' ] );
	$total_submitions = array_sum( $stats['datasets'][ 'submission' ][ 'data' ] );
	$engage_conversion = round( ( $total_submitions / $total_engage ) * 100, 1);
	$load_conversion = round( ( $total_submitions / $total_loads ) * 100, 1);
	$view_conversion = round( ( $total_submitions / $total_views ) * 100, 1);
	
	$engage_load = round( ( $total_engage / $total_loads ) * 100, 1);
	$engage_view = round( ( $total_engage / $total_views ) * 100, 1);

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

	if( !empty( $engage_conversion ) ){
		if( $engage_conversion > 0 ){
			$stats['conversion_story'] .= sprintf( __( '%s of visitors actively engage the form and %s of engaged users go on to a submission. ', 'formworks' ),
				'<strong>' . $engage_load . '%</strong>',
				'<strong>' . $engage_conversion . '%</strong>'
			 );
			$stats['conversion_story'] .= '<br>';
		}
	}
	if( !empty( $load_conversion ) ){
		$stats['conversion_story'] .= sprintf( esc_html__( 'That is a conversion rate of %s from load', 'formworks' ), '<strong>' . $load_conversion . '%</strong>' );

		if( $engage_conversion > 0 ){
			$stats['conversion_story'] .= ' ';
			$stats['conversion_story'] .= sprintf( esc_html__( 'and leaves a %s abandon rate.', 'formworks' ),
				'<strong>' . ( 100 - $engage_conversion ) . '%</strong>'
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
		$stats['conversion_story'] .= sprintf( esc_html__( "Therefore %s of page visitors don't even see the form. ", 'formworks' ), '<strong>' . $view_percent . '%</strong>' );
		$stats['conversion_story'] .= '</p>';

		$stats['conversion_story'] .= '<p>';
		if( !empty( $view_conversion ) ){
			$stats['conversion_story'] .= sprintf( esc_html__( 'Of those that do see the form, the conversion rate is %s. ', 'formworks' ), '<strong>'.$view_conversion . '%</strong>' );
		}
		if( !empty( $engage_conversion ) ){

			if( $engage_conversion > 0 ){
				$stats['conversion_story'] .= sprintf( esc_html__( 'This means that %s of visitors who see the form, actively engage it.', 'formworks' ),
					'<strong>' . $engage_view . '%</strong>'
				 );
			}
		}
		$stats['conversion_story'] .= '</p>';

	}else{
		$stats['conversion_story'] .= '<p>';
		$stats['conversion_story'] .= esc_html__( 'Form loads and views are the same. This indicates that the form is visible on page load.', 'formworks' );
		$stats['conversion_story'] .= '</p>';
	}

	return $stats['conversion_story'];

}
