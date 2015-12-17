<div class="formworks-main-headercaldera">
		<h1 class="formworks-main-title">
		<?php esc_html_e( 'Formworks', 'formworks' ); ?>
		<span class="formworks-version">
			<?php echo FRMWKS_VER; ?>
		</span>
		<span class="formworks-nav-separator"></span>
			
		<span class="add-new-h2 wp-baldrick" data-action="frmwks_save_config" data-load-element="#formworks-save-indicator" data-callback="frmwks_handle_save" data-before="frmwks_get_config_object" >
			<?php esc_html_e('Save Changes', 'formworks') ; ?>
		</span>

		<span class="formworks-nav-separator"></span>
		
		<span style="position: absolute; top: 5px;" id="formworks-save-indicator">
			<span style="float: none; margin: 10px 0px -5px 10px;" class="spinner"></span>
		</span>

	</h1>


		<div class="updated_notice_box">
		<?php esc_html_e( 'Updated Successfully', 'formworks' ); ?>
	</div>
	<div class="error_notice_box">
		<?php esc_html_e( 'Could not save changes.', 'formworks' ); ?>
	</div>
	<ul class="formworks-header-tabs formworks-nav-tabs">
				
		
				
	</ul>

	<span class="wp-baldrick" id="formworks-field-sync" data-event="refresh" data-target="#formworks-main-canvas" data-before="frmwks_canvas_reset" data-callback="frmwks_canvas_init" data-type="json" data-request="#formworks-live-config" data-template="#main-ui-template"></span>
</div>
<div class="formworks-sub-headercaldera">
	<ul class="formworks-sub-tabs formworks-nav-tabs">
		<li class="{{#is _current_tab value="#formworks-panel-forms"}}active {{/is}}formworks-nav-tab">
			<a href="#formworks-panel-forms">
				<?php esc_html_e('Forms', 'formworks') ; ?>
			</a>
		</li>
		<li class="{{#is _current_tab value="#formworks-panel-analytics"}}active {{/is}}formworks-nav-tab">
			<a href="#formworks-panel-analytics">
				<?php esc_html_e('Analytics', 'formworks') ; ?>
			</a>
		</li>
		<li class="{{#is _current_tab value="#formworks-panel-tools"}}active {{/is}}formworks-nav-tab">
			<a href="#formworks-panel-tools">
				<?php esc_html_e('Tools', 'formworks') ; ?>
			</a>
		</li>
		<li class="{{#is _current_tab value="#formworks-panel-about"}}active {{/is}}formworks-nav-tab">
			<a href="#formworks-panel-about">
				<?php esc_html_e('About', 'formworks') ; ?>
			</a>
		</li>		
	</ul>
</div>
<form class="caldera-main-form " id="formworks-main-form" action="?page=formworks" method="POST">
	<?php wp_nonce_field( 'formworks', 'formworks-setup' ); ?>
	<input type="hidden" value="formworks" name="id" id="formworks-id">

	<input type="hidden" value="{{_current_tab}}" name="_current_tab" id="formworks-active-tab">

	
	<div id="formworks-panel-forms" class="formworks-editor-panel" {{#if _current_tab}}{{#is _current_tab value="#formworks-panel-forms"}}{{else}} style="display:none;" {{/is}}{{/if}}>	
		<h4>
			<?php esc_html_e('Form Statistics', 'formworks') ; ?>
			<small class="description">
				<?php esc_html_e('Analytics', 'formworks') ; ?>
			</small>
		</h4>
		<?php
			/**
			 * Include the settings template
			 */
			include FRMWKS_PATH . 'includes/templates/general-settings.php';
		?>
	</div>
		

	<div id="formworks-panel-analytics" class="formworks-editor-panel" {{#if _current_tab}}{{#is _current_tab value="#formworks-panel-analytics"}}{{else}} style="display:none;" {{/is}}{{/if}}>	
		<h4>
			<?php esc_html_e( 'Google Analytics Goals', 'formworks' ); ?>
			<small class="description">
				<?php esc_html_e('How to setup goals in Google Analytics', 'formworks') ; ?>
			</small>	
		</h4>
		<?php
			/**
			 * Include the analytics template
			 */
			include FRMWKS_PATH . 'includes/templates/analytics-panel.php';
		?>
	</div>


	<div id="formworks-panel-tools" class="formworks-editor-panel" {{#if _current_tab}}{{#is _current_tab value="#formworks-panel-tools"}}{{else}} style="display:none;" {{/is}}{{/if}}>	
		<h4>
			<?php esc_html_e( 'Tools and Utilities', 'formworks' ); ?>
			<small class="description">
				<?php esc_html_e('General Maintanence Options and Settings', 'formworks') ; ?>
			</small>	
		</h4>
		<?php
			/**
			 * Include the tools template
			 */
			include FRMWKS_PATH . 'includes/templates/tools-panel.php';
		?>
	</div>	
	<div id="formworks-panel-about" class="formworks-editor-panel" {{#if _current_tab}}{{#is _current_tab value="#formworks-panel-about"}}{{else}} style="display:none;" {{/is}}{{/if}}>	
		<h4>
			<?php esc_html_e( 'About Formworks', 'formworks' ); ?>
		</h4>
		<?php
			/**
			 * Include the about template
			 */
			include FRMWKS_PATH . 'includes/templates/about-panel.php';
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
