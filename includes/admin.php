<?php
/**
 * Main admin interface for selecting items to edit/ creating or deleting items.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */
?>

<div class="wrap formworks-calderaadmin-wrap" id="formworks-admin--wrap">
	<div class="formworks-main-headercaldera">
		<h2>
			<?php _e( 'Formworks', 'formworks' ); ?>
			<span class="formworks-version">
				<?php echo FRMWKS_VER; ?>
			</span>
			<span class="add-new-h2 wp-baldrick" data-modal="new-formworks" data-modal-height="285" data-modal-width="402" data-modal-buttons='<?php _e( 'Create Form View', 'formworks' ); ?>|{"data-action":"frmwks_create_formworks","data-before":"frmwks_create_new_formworks", "data-callback": "frmwks_redirect_to_formworks"}' data-modal-title="<?php _e('New Form View', 'formworks') ; ?>" data-request="#new-formworks-form">
				<?php _e('Add New', 'formworks') ; ?>
			</span>
			<span class="formworks-nav-separator"></span>
			<span class="add-new-h2 wp-baldrick" data-modal="import-formworks" data-modal-height="auto" data-modal-width="380" data-modal-buttons='<?php _e( 'Import Form View', 'formworks' ); ?>|{"id":"frmwks_import_init", "data-action":"frmwks_create_formworks","data-before":"frmwks_create_new_formworks", "data-callback": "frmwks_redirect_to_formworks"}' data-modal-title="<?php _e('Import Form View', 'formworks') ; ?>" data-request="frmwks_start_importer" data-template="#import-formworks-form">
				<?php _e('Import', 'formworks') ; ?>
			</span>
		</h2>
	</div>

<?php

	$formworkss = \calderawp\frmwks\options::get_registry();
	if( empty( $formworkss ) ){
		$formworkss = array();
	}

	global $wpdb;
	
	foreach( $formworkss as $formworks_id => $formworks ){

?>

	<div class="formworks-card-item" id="formworks-<?php echo $formworks[ 'id' ]; ?>">
		<span class="dashicons dashicons-tablet formworks-card-icon"></span>
		<div class="formworks-card-content">
			<h4>
				<?php echo $formworks[ 'name' ]; ?>
			</h4>
			<div class="description">
				<?php echo $formworks[ 'slug' ]; ?>
			</div>
			<div class="description">&nbsp;</div>
			<div class="formworks-card-actions">
				<div class="row-actions">
					<span class="edit">
						<a href="?page=formworks&amp;download=<?php echo $formworks[ 'id' ]; ?>&formworks-export=<?php echo wp_create_nonce( 'formworks' ); ?>" target="_blank"><?php _e('Export', 'formworks'); ?></a> |
					</span>
					<span class="edit">
						<a href="?page=formworks&amp;edit=<?php echo $formworks[ 'id' ]; ?>"><?php _e('Edit', 'formworks'); ?></a> |
					</span>
					<span class="trash confirm">
						<a href="?page=formworks&amp;delete=<?php echo $formworks[ 'id' ]; ?>" data-block="<?php echo $formworks[ 'id' ]; ?>" class="submitdelete">
							<?php _e('Delete', 'formworks'); ?>
						</a>
					</span>
				</div>
				<div class="row-actions" style="display:none;">
					<span class="trash">
						<a class="wp-baldrick" style="cursor:pointer;" data-action="frmwks_delete_formworks" data-callback="frmwks_remove_deleted" data-block="<?php echo $formworks['id']; ?>" class="submitdelete"><?php _e('Confirm Delete', 'formworks'); ?></a> | </span>
					<span class="edit confirm">
						<a href="?page=formworks&amp;edit=<?php echo $formworks['id']; ?>">
							<?php _e('Cancel', 'formworks'); ?>
						</a>
					</span>
				</div>
			</div>
		</div>
	</div>

	<?php } ?>

</div>
<div class="clear"></div>
<script type="text/javascript">
	
	function frmwks_create_new_formworks(el){
		var formworks 	= jQuery(el),
			name 	= jQuery("#new-formworks-name"),
			slug 	= jQuery('#new-formworks-slug'),
			form 	= jQuery('#new-formworks-form-id'),
			view_type 	= jQuery('#new-formworks-type'),
			imp 	= jQuery('#new-formworks-import'); 

		if( imp.length ){
			if( !imp.val().length ){
				return false;
			}
			formworks.data('import', imp.val() );
			return true;
		}

		if( slug.val().length === 0 ){
			name.focus();
			return false;
		}
		if( slug.val().length === 0 ){
			slug.focus();
			return false;
		}
		if( form.val().length === 0 ){
			form.focus();
			return false;
		}
		if( view_type.val().length === 0 ){
			view_type.focus();
			return false;
		}		

		formworks.data('name', name.val() ).data('slug', slug.val() ).data('form', form.val() ).data('view_type', view_type.val() );

	}

	function frmwks_redirect_to_formworks(obj){
		if( obj.data.success ){

			obj.params.trigger.prop('disabled', true).html('<?php _e('Loading Form View', 'formworks'); ?>');
			window.location = '?page=formworks&edit=' + obj.data.data.id;

		}else{

			jQuery('#new-block-slug').focus().select();
			
		}
	}
	function frmwks_remove_deleted(obj){

		if( obj.data.success ){
			jQuery( '#formworks-' + obj.data.data.block ).fadeOut(function(){
				jQuery(this).remove();
			});
		}else{
			alert('<?php echo __('Sorry, something went wrong. Try again.', 'formworks'); ?>');
		}
	}
	function frmwks_start_importer(){
		return {};
	}
</script>
<script type="text/html" id="new-formworks-form">
	<div class="formworks-config-group">
		<label>
			<?php _e('Form View Name', 'formworks'); ?>
		</label>
		<input type="text" name="name" id="new-formworks-name" data-sync="#new-formworks-slug" autocomplete="off">
	</div>
	<div class="formworks-config-group">
		<label>
			<?php _e('Form View Slug', 'formworks'); ?>
		</label>
		<input type="text" name="slug" id="new-formworks-slug" data-format="slug" autocomplete="off">
	</div>
	<div class="formworks-config-group">
		<label>
			<?php _e('Bound Form', 'formworks'); ?>
		</label>
		<select id="new-formworks-form-id" name="form" style="width:190px;">
		<option></option>
		<?php
			$forms = Caldera_Forms::get_forms();
			foreach ($forms as $form_id => $form) {
				echo '<option value="' . $form_id . '">' . $form['name'] . '</option>';
			}
		?>
		</select>
	</div>
	<div class="formworks-config-group">
		<label>
			<?php _e('View Type', 'formworks'); ?>
		</label>
		<select id="new-formworks-type" name="type" style="width:190px;">
		<option></option>
		<optgroup label="<?php echo esc_attr( __('Admin', 'formworks') ); ?>">
			<option value="admin_stats"><?php _e('Analytics', 'formworks'); ?></option>
			<option value="admin_entry"><?php _e('Entry Management', 'formworks'); ?></option>
			<option value="admin_report"><?php _e('Reporting', 'formworks'); ?></option>
		</optgroup>
		<optgroup label="<?php echo esc_attr( __('Front', 'formworks') ); ?>">
			<option value="front_table"><?php _e('Entry Table', 'formworks'); ?></option>
			<option value="front_custom"><?php _e('Custom Entry Viewer', 'formworks'); ?></option>
		</optgroup>
		</select>
	</div>

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

						$('#new-formworks-import').val( contents );
						$('#frmwks_import_init').prop('disabled', false).removeClass('disabled');
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
