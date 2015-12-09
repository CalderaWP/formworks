<form class="caldera-main-form has-sub-nav" id="formworks-main-form" action="?page=formworks" method="POST">

<div class="formworks-main-headercaldera" style="height: 46px;">
		<h1 class="formworks-main-title">
		<a style="text-decoration: none; color: rgb(219, 68, 55);" href="<?php echo admin_url( 'admin.php?page=formworks&form=' ); ?>"><?php _e('Formworks', 'formworks') ?></a>
		/
		<?php echo $formworks['name']; ?>
		<span class="formworks-version">
			<?php echo FRMWKS_VER; ?>
		</span>		
	</h1>

<div style="position: relative; top: 11px; display:inline-block;">
	<input type="hidden" name="filters[device]" value="">
	<ul style="margin: 0px; padding: 5px 0 26px;">
		<li style="float: left;">
			<label class="add-new-h2 formworks-filter-button {{#if filters/device/computer}}active{{/if}}" ><input type="checkbox" style="display:none;" class="preset-check" value="1" name="filters[device][computer]" {{#if filters/device/computer}}checked="checked"{{/if}}><span style="margin: 1px -2px;" class="dashicons dashicons-desktop"></span></label>
		</li>
		<li style="float: left;">
			<label class="add-new-h2 formworks-filter-button {{#if filters/device/tablet}}active{{/if}}" ><input type="checkbox" style="display:none;" class="preset-check" value="1" name="filters[device][tablet]" {{#if filters/device/tablet}}checked="checked"{{/if}}><span style="margin: 1px -2px;" class="dashicons dashicons-tablet"></span></label>
		</li>
		<li style="float: left;">
			<label class="add-new-h2 formworks-filter-button {{#if filters/device/phone}}active{{/if}}" ><input type="checkbox" style="display:none;" class="preset-check" value="1" name="filters[device][phone]" {{#if filters/device/phone}}checked="checked"{{/if}}><span style="margin: 1px -2px;" class="dashicons dashicons-smartphone"></span></label>
		</li>
		<li style="float: left;width:20px;"></li>
		<li style="float: left;">
			<label class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="this_week"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="this_week" name="filters[date][preset]" {{#is filters/date/preset value="this_week"}}checked="checked"{{/is}}><?php esc_html_e('This Week', 'formworks' ); ?></label>
		</li>
		<li style="float: left;">
			<label class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="this_month"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="this_month" name="filters[date][preset]" {{#is filters/date/preset value="this_month"}}checked="checked"{{/is}}><?php esc_html_e('This Month', 'formworks' ); ?></label>
		</li>
		<li style="float: left;">
			<label class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="last_month"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="last_month" name="filters[date][preset]" {{#is filters/date/preset value="last_month"}}checked="checked"{{/is}}><?php esc_html_e('Last Month', 'formworks' ); ?></label>
		</li>

		<li style="float: left;">
			<label id="filter-custom-range" class="add-new-h2 date-range formworks-filter-button {{#is filters/date/preset value="custom"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="custom" name="filters[date][preset]" {{#is filters/date/preset value="last_month"}}checked="checked"{{/is}}><?php esc_html_e('Custom Range', 'formworks' ); ?></label>
		</li>
		<li style="float: left;">
			<div style="display:none; margin: -4px 0px 0px 4px;" class="formwork-datepicker input-daterange input-group" id="formworks-range-datepicker">
			    <input style="width: 120px; vertical-align: middle;" type="text" class="formworks-date-input" name="filters[date][start]" value="{{filters/date/start}}" />
			    <span style="display: inline-block; background: rgb(224, 224, 224) none repeat scroll 0% 0%; margin: 0 -5px; padding: 3px 6px 6px;"><?php esc_html_e( 'to', 'formworks' ); ?></span>
			    <input style="width: 120px; vertical-align: middle;" type="text" class="formworks-date-input" name="filters[date][end]" value="{{filters/date/end}}" />
			</div>
		</li>

		<li class="wp-baldrick apply-filters" data-for="#filter-reload-trigger" style="display:none; float: left; height: 38px; margin: -16px 0px 0px 6px; padding: 16px 0px 0px 5px; border-left: 1px solid rgb(226, 226, 226);">
			<label class="add-new-h2 formworks-filter-button"><?php esc_html_e('Apply Filters', 'formworks'); ?></label>
		</li>
	</ul>
</div>

{{#script}}
jQuery( function( $ ) {
	$('.input-daterange').datepicker({
		format: "yyyy-mm-dd",
		orientation: "bottom left",
		autoclose: true
	}).on('changeDate', function(){
		$('#filter-custom-range').trigger('click');
	});
});

{{/script}}
	
	
<span class="wp-baldrick" id="formworks-field-sync" data-event="refresh" data-target="#formworks-main-canvas" data-before="frmwks_canvas_reset" data-callback="frmwks_canvas_init" data-type="json" data-request="#formworks-live-config" data-template="#main-ui-template"></span>

<input type="hidden" value="{{#if module_data}}{{json module_data}}{{/if}}" name="module_data"
	class="wp-baldrick stat-module"
	data-before="frmwks_get_filters"
	data-load-class="loading-module"
	data-action="frmwks_module_data"
	{{#unless module_data}}data-autoload="true"{{/unless}}
	id="core-module-data"
	data-target="#core-module-data"
	data-event="reload"
	data-live-sync="true"
	id="filter-reload-trigger"
>

</div>
<div class="formworks-sub-headercaldera">
	<ul class="formworks-sub-tabs formworks-nav-tabs">
		<li class="{{#is _current_tab value="#formworks-panel-stats"}}active {{/is}}formworks-nav-tab">
			<span>
				<h4 style="display:inline;">
					<?php _e('Form Statistics', 'formworks') ; ?>
					<small class="description" style="color: rgba(255,255,255,0.9);">
						<?php _e('Analytics', 'formworks') ; ?>
					</small>
				</h4>
			</span>
		</li>	
		<li>{{>filters_detail}}</li>
	</ul>
</div>

	<?php wp_nonce_field( 'formworks', 'formworks-setup' ); ?>
	<input type="hidden" value="pinned" name="id" id="formworks-id">
	<input type="hidden" name="quick_stats" id="formworks-quick-stats" value="{{#if quick_stats}}{{json quick_stats}}{{/if}}" data-live-sync="true">
	<input type="hidden" name="form_id" value="{{form_id}}">
	<input type="hidden" name="form_slug" value="{{form_slug}}">

	<input type="hidden" value="{{_current_tab}}" name="_current_tab" id="formworks-active-tab">

	<div id="formworks-panel-stats" class="formworks-editor-panel" {{#is _current_tab value="#formworks-panel-stats"}}{{else}} style="display:none;" {{/is}}>		

		<?php
			/**
			 * Include the access-panel
			 */
			include FRMWKS_PATH . 'includes/templates/pinned-stats-panel.php';
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
