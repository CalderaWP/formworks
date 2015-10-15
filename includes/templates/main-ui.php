<div class="formworks-main-headercaldera">
		<h2 class="formworks-main-title">
		<?php _e( 'Formworks', 'formworks' ); ?>
		<span class="formworks-version">
			<?php echo FRMWKS_VER; ?>
		</span>
		<span class="formworks-nav-separator"></span>
			
		<span class="add-new-h2 wp-baldrick" data-action="frmwks_save_config" data-load-element="#formworks-save-indicator" data-callback="frmwks_handle_save" data-before="frmwks_get_config_object" >
			<?php _e('Save Changes', 'formworks') ; ?>
		</span>

		<span class="formworks-nav-separator"></span>

		<a class="add-new-h2" href="?page=formworks&amp;download=<?php echo $formworks[ 'id' ]; ?>&formworks-export=<?php echo wp_create_nonce( 'formworks' ); ?>"><?php _e('Export', 'formworks'); ?></a>

		<span class="add-new-h2 wp-baldrick" data-modal="import-formworks" data-modal-height="auto" data-modal-width="380" data-modal-buttons='<?php _e( 'Import Form View', 'formworks' ); ?>|{"id":"frmwks_import_init", "data-request":"frmwks_create_formworks", "data-modal-autoclose" : "import-formworks"}' data-modal-title="<?php _e('Import Form View', 'formworks') ; ?>" data-request="frmwks_start_importer" data-template="#import-formworks-form">
			<?php _e('Import', 'formworks') ; ?>
		</span>

		<span class="formworks-nav-separator"></span>
		
		<span style="position: absolute; top: 5px;" id="formworks-save-indicator">
			<span style="float: none; margin: 10px 0px -5px 10px;" class="spinner"></span>
		</span>

	</h2>


		<div class="updated_notice_box">
		<?php _e( 'Updated Successfully', 'formworks' ); ?>
	</div>
	<div class="error_notice_box">
		<?php _e( 'Could not save changes.', 'formworks' ); ?>
	</div>
	<ul class="formworks-header-tabs formworks-nav-tabs">
				
		
				
	</ul>

	<span class="wp-baldrick" id="formworks-field-sync" data-event="refresh" data-target="#formworks-main-canvas" data-before="frmwks_canvas_reset" data-callback="frmwks_canvas_init" data-type="json" data-request="#formworks-live-config" data-template="#main-ui-template"></span>
</div>

<form class="caldera-main-form " id="formworks-main-form" action="?page=formworks" method="POST">
	<?php wp_nonce_field( 'formworks', 'formworks-setup' ); ?>
	<input type="hidden" value="formworks" name="id" id="formworks-id">

	<input type="hidden" value="{{_current_tab}}" name="_current_tab" id="formworks-active-tab">

	

	<div id="formworks-panel-flow" class="formworks-editor-panel">		
		<h4>
			<?php _e('Setup Formworks Pages', 'formworks') ; ?>
			<small class="description">
				<?php _e('General Settings', 'formworks') ; ?>
			</small>
		</h4>
		<?php
			/**
			 * Include the settings template
			 */
			include FRMWKS_PATH . 'includes/templates/general-settings.php';
		?>
	</div>
		

</form>

{{#unless _current_tab}}
	{{#script}}
		jQuery(function($){
			$('.formworks-nav-tab').first().trigger('click').find('a').trigger('click');
		});
	{{/script}}
{{/unless}}
