<?php
/**
 * Main edit interface for single items.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

$formworks = \calderawp\frmwks\options::get_single( 'formworks' );

if( class_exists( 'Caldera_Forms' ) ){
	$forms = Caldera_Forms::get_forms();
	$formworks['forms']['caldera'] = array(
		'name' => __('Caldera Forms', 'caldera-forms'),
		'forms' => array()
	);
	foreach( $forms as $form ){
		$formworks['forms']['caldera']['forms'][ $form['ID'] ] = $form['name'];
	}
}
if( class_exists('RGFormsModel') ){
	$forms = RGFormsModel::get_forms( null, 'title' );
	$formworks['forms']['gravity'] = array(
		'name' => __('Gravity Forms', 'gravityforms'),
		'forms' => array()
	);
	foreach( $forms as $form ){
		$formworks['forms']['gravity']['forms'][ 'gform_' . $form->id ] = $form->title;
	}
}
if( class_exists( 'NF_Forms' ) ){
	$nforms = new NF_Forms();
	$nforms = $nforms->get_all();
	$formworks['forms']['ninja'] = array(
		'name' => __('Ninja Forms', 'ninja-forms'),
		'forms' => array()
	);
	foreach ($nforms as $form) {
		$formworks['forms']['ninja']['forms'][ 'ninja_' . $form ]	= Ninja_Forms()->form( $form )->get_setting( 'form_title' );
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
