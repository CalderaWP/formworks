<?php
global $wpdb;
/**
 * Main edit interface for single items.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */


// simple make stuff
$types = array(
	'view',
	'view',
	'view',
	'loaded',
	'loaded',
	'loaded',
	'submission',
	'engage',
	'engage',
);



$formworks = \calderawp\frmwks\options::get_single( 'formworks' );
$formworks['_current_tab'] = '#formworks-panel-forms';


$formworks['forms'] = apply_filters( 'formworks_get_forms', array() );


// add slug
foreach( $formworks['forms'] as $slug => &$forms_set ){
	$forms_set[ 'slug' ] = $slug;

	foreach( $forms_set[ 'forms' ] as $form_id => $form ){		
		// hack to populate
		for( $i = 0; $i < 20; $i++){

			$entry = array(
				'form_id' => $form_id,
				'prefix' => $slug,
				'user_id' => 1,
				'user_key' => '56334b88a6dc8',
				'datestamp' => date('Y-m-d H:i:s', strtotime("-" . rand(0, DAY_IN_SECONDS) ." seconds")),
				'meta_key' => $types[ rand(0, 8) ],
				'meta_value' => 1
			);
			//$wpdb->insert( 'wp_formworks_tracker', $entry );

		}



		$list=array();
		$activity_list = array();
		$limit = count( $activity_list );
		$activity = $wpdb->get_results( "SELECT 
				COUNT( `id` ) as `total`
			FROM
				`{$wpdb->prefix}formworks_tracker` 
			WHERE 
				
				`form_id` = '{$form_id}'
				&&
				`prefix` = '{$slug}'

			GROUP BY
			SUBSTR( `datestamp`, 1, 14) DESC
			ORDER BY
			`datestamp` DESC
			
			;", ARRAY_A );

			foreach( $activity as $line_key => $line ) {
				$activity_list[] = $line['total'];
			}
		
			$forms_set['forms'][ $form_id ] = array(
				'name' => $form,
				'activity' => implode(',',$activity_list)
			);

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
			`meta_key` IN ( 'view','submission','loaded','engage' ) &&
			`prefix` = %s &&
			`form_id` = %s && 
			`meta_value` != ''
			GROUP BY `meta_key`
			;", $slug, $form_id );
			$summary = $wpdb->get_results( $query );
			foreach( $summary as $summ ){
				$forms_set['forms'][ $form_id ][ $summ->meta_key ] = $summ->total;
			}
			$forms_set['forms'][ $form_id ][ 'conversion' ] = 0;
			if( !empty( $forms_set['forms'][ $form_id ][ 'engage' ] ) && !empty( $forms_set['forms'][ $form_id ][ 'submission' ] ) ){
				$forms_set['forms'][ $form_id ][ 'conversion' ] = round( ( $forms_set['forms'][ $form_id ][ 'submission' ] / $forms_set['forms'][ $form_id ][ 'engage' ] ) * 100, 1);
			}

		}


}

?>
<div class="wrap formworks-calderamain-canvas" id="formworks-main-canvas">
	<span class="wp-baldrick spinner" style="float: none; display: block;" data-target="#formworks-main-canvas" data-before="frmwks_canvas_reset" data-callback="frmwks_canvas_init" data-type="json" data-request="#formworks-live-config" data-event="click" data-template="#main-ui-template" data-autoload="true"></span>
</div>

<div class="clear"></div>

<input type="hidden" class="clear" autocomplete="off" id="formworks-live-config" style="width:100%;" value="<?php echo esc_attr( json_encode($formworks) ); ?>">

<script type="text/html" id="main-ui-template">
	<?php
		/**
		 * Include main UI
		 */
		include FRMWKS_PATH . 'includes/templates/main-ui.php';
	?>
</script>
<script type="text/html" id="rebuild-db-tml">
	<div class="error" style="color: rgb(255, 0, 0);"><p><strong>WARNING</strong>: This will destroy all tracked data and rebuild the database structures.</p></div>
	<button class="button button-primary wp-baldrick" data-load-class="loading_tool_action" data-action="frmwks_rebuild_database" data-active-class="disabled" data-target="#warning_baldrickModalBody" type="button" style="width: 100%; margin: 34px 0px 0px;"><?php _e('Understood. Proceed...', 'formworks'); ?></button>
</script>
<script type="text/x-handlebars-template" id="edit-paginator-template">
		<input type="hidden" name="_id" value="{{_id}}">
		<input type="hidden" name="_node_point" value="{{_node_point}}">
		<div class="formworks-config-group">
			<label for="formworks-paginator-type">
				<?php _e( 'Type', 'formworks' ); ?>
			</label>
			<select name="type">
				<option value="standard" {{#is type value="standard"}}selected="selected"{{/is}}>Standard</option>
				<option value="alphabetical" {{#is type value="alphabetical"}}selected="selected"{{/is}}>Alphabetical</option>
				<option value="date" {{#is type value="date"}}selected="selected"{{/is}}>Date</option>
			</select>
		</div>
		<div class="formworks-config-group">
			<label for="formworks-paginator-type">
				<?php _e( 'Position', 'formworks' ); ?>
			</label>
			<select name="position">
				<option value="top" {{#is type value="top"}}selected="selected"{{/is}}>Top</option>
				<option value="bottom" {{#is type value="bottom"}}selected="selected"{{/is}}>Bottom</option>
			</select>
		</div>
</script>
