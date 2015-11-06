<?php
/**
 * Main edit interface for admin page.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

$formworks = \calderawp\frmwks\options::get_single( $formwork_id );
$modules = apply_filters( 'formworks_stat_modules', array() );

// simplyfy creation
$formworks['id'] = '__' . $formwork_id;
$formworks['_current_tab'] = '#formworks-panel-stats';

$formworks['forms'] = apply_filters( 'formworks_get_forms', array() );
$form_parts = explode( '_', $formwork_id, 2 );

$formworks['form_id'] = $form_parts[1];
$formworks['form_slug'] = $form_parts[0];

$formworks['filters']['date']['start'] = date('Y-m-d', strtotime( "-7 days" ) );
$formworks['filters']['date']['end'] = date('Y-m-d', strtotime( "tomorrow" ) );
$formworks['filters']['date']['preset'] = 'this_week';

if( empty( $formworks['forms'][ $form_parts[0] ]['forms'][$form_parts[1]] ) ){
	wp_die( __('Invalid form or form removed', 'formworks' ) );
}

$formworks['name'] = $formworks['forms'][ $form_parts[0] ]['forms'][$form_parts[1]];
if( empty( $formworks['page'] ) ){
	$formworks['page'] = 1;
}
$formworks['data'] = null;

$formworks['legend']['engage'] = $formworks['legend']['loaded'] = $formworks['legend']['submission'] = $formworks['legend']['view'] = 'true';

$formworks['data'] = false;
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
		include FRMWKS_PATH . 'includes/templates/pinned-main-ui.php';
	?>	
</script>

<script type="text/javascript">
	frmwks_is_view = true;
	function frmwks_start_importer(){
		return {};
	}
	function frmwks_create_formworks(){
		jQuery('#formworks-field-sync').trigger('refresh');
		jQuery('#frmwks-save-button').trigger('click');
	}
</script>
<?php
foreach( $modules as $module_slug => $module ){
	if( !file_exists( $module['template'] ) )
		continue;
	?>
	<script type="text/html" data-handlebars-partial="<?php echo $module_slug; ?>">
	<div 
		id="<?php echo $module_slug; ?>-module"
		class="wp-baldrick stat-module"
		data-event="reload"
		data-before="frmwks_get_filters"
		data-load-class="loading-module"
		data-action="frmwks_module_data"
		data-module="<?php echo $module_slug; ?>"
		data-template="#<?php echo $module_slug; ?>-tmpl"
		data-target="#<?php echo $module_slug; ?>-module"
		data-autoload="true"
		style="position: relative;min-height:20px;"
	></div>
	</script>
	<script type="text/html" id="<?php echo $module_slug; ?>-tmpl">
	<?php include $module['template']; ?>
	</script>
	<?php
}
?>
