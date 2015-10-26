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

// simplyfy creation
$formworks['id'] = '__' . $formwork_id;
$formworks['_current_tab'] = '#formworks-panel-stats';

$formworks['forms'] = apply_filters( 'formworks_get_forms', array() );
$form_parts = explode( '_', $formwork_id, 2 );

if( empty( $formworks['forms'][ $form_parts[0] ]['forms'][$form_parts[1]] ) ){
	wp_die( __('Invalid form or form removed', 'formworks' ) );
}

$formworks['name'] = $formworks['forms'][ $form_parts[0] ]['forms'][$form_parts[1]];


//$data = \calderawp\frmwks\tracker::get_main_stats( $formwork_id );
//var_dump( $data['datasets'] );
//die;

if( empty( $formworks['page'] ) ){
	$formworks['page'] = 1;
}
$formworks['data'] = null;

$formworks['legend']['engage'] = $formworks['legend']['loaded'] = $formworks['legend']['submission'] = $formworks['legend']['view'] = 'true';


/*$formworks['data'] = \calderawp\frmwks\options::get_entries( $formworks['form'],1, 1000, 'CF');
global $wpdb;

$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "formworks_tracker`" );
foreach( $formworks['data']['entries'] as $entry ){
		$user = get_user_by( 'email', $entry['data']['email'] );
		if( !empty( $user ) ){
			$user_id = $user->ID;
		}else{
			$user_id = 0;
		}
		$data = array(
			'form_id'	=>	$formworks['form'],
			'user_id'	=>	$user_id,
			'user_key'	=>	md5( $entry['data']['email'] ),
			'meta_value'=>	'1',
			'datestamp'=> date( 'Y-m-d H:i:s', strtotime( $entry['_date'] ) ),
		);
	
		$random_seed = rand(2, 7);
		$data['meta_key'] = 'load';
		$wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );			

		for( $rdm = 0; $rdm < $random_seed; $rdm++ ){
			$data['meta_key'] = 'view';
			$wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );			
			$data['meta_key'] = 'loaded';
			$wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );
		}
		$random_seed = rand(1, 2);
		for( $rdm = 0; $rdm < $random_seed; $rdm++ ){		
			$data['meta_key'] = 'engage';
			$wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );			
		}

		$data['meta_key'] = 'submission';
		$data['meta_value'] = rand( 90, 200 );
		$wpdb->insert( $wpdb->prefix . "formworks_tracker", $data );
}*/
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
<script type="text/html" id="import-formworks-form">
	<div class="import-tester-config-group">
		<input id="new-formworks-import-file" type="file" class="regular-text">
		<input id="new-formworks-import" value="" name="import" type="hidden">
	</div>
	{{#script}}
		jQuery( function($){

			$('#frmwks_import_init').prop('disabled', true).addClass('disabled');

			$('#new-formworks-import-file').on('change', function(){
				$('#frmwks_import_init').prop('disabled', true).addClass('disabled');
				var input = $(this),
					f = this.files[0],
				contents;

				if (f) {
					var r = new FileReader();
					r.onload = function(e) { 
						contents = e.target.result;
						var data;
						 try{ 
						 	data = JSON.parse( contents );
						 } catch(e){};
						 
						 if( !data || ! data['formworks-setup'] ){
						 	alert("<?php echo esc_attr( __('Not a valid Form View export file.', 'formworks') ); ?>");
						 	input[0].value = null;
							return false;
						 }

						$('#formworks-live-config').val( contents );						
						$('#frmwks_import_init').prop('disabled', false).removeClass('disabled');
					}
					if( f.type !== 'application/json' ){
						alert("<?php echo esc_attr( __('Not a valid Form View export file.', 'formworks') ); ?>");
						this.value = null;
						return false;
					}
					r.readAsText(f);
				} else { 
					alert("Failed to load file");
					return false;
				}
			});

		});
	{{/script}}
</script>
