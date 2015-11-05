<?php
/**
 * partioal template for Main Stats
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

?>
<h4 style="float: left; margin: 0 18px 0px 0px;">
	<?php _e('Form Statistics', 'formworks') ; ?>
	<small class="description">
		<?php _e('Analytics', 'formworks') ; ?>
	</small>
</h4>


<div style="position: relative; top: -5px;">
	<input type="hidden" name="filters[device]" value="">

	<label class="add-new-h2 formworks-filter-button {{#if filters/device/computer}}active{{/if}}" ><input type="checkbox" style="display:none;" class="preset-check" value="1" name="filters[device][computer]" {{#if filters/device/computer}}checked="checked"{{/if}}><span style="margin: 5px -2px;" class="dashicons dashicons-desktop"></span></label>
	<label class="add-new-h2 formworks-filter-button {{#if filters/device/tablet}}active{{/if}}" ><input type="checkbox" style="display:none;" class="preset-check" value="1" name="filters[device][tablet]" {{#if filters/device/tablet}}checked="checked"{{/if}}><span style="margin: 5px -2px;" class="dashicons dashicons-tablet"></span></label>
	<label class="add-new-h2 formworks-filter-button {{#if filters/device/phone}}active{{/if}}" ><input type="checkbox" style="display:none;" class="preset-check" value="1" name="filters[device][phone]" {{#if filters/device/phone}}checked="checked"{{/if}}><span style="margin: 5px -2px;" class="dashicons dashicons-smartphone"></span></label>

	&nbsp;


	<label class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="this_week"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="this_week" name="filters[date][preset]" {{#is filters/date/preset value="this_week"}}checked="checked"{{/is}}><?php _e('This Week', 'formworks' ); ?></label>
	<label class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="this_month"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="this_month" name="filters[date][preset]" {{#is filters/date/preset value="this_month"}}checked="checked"{{/is}}><?php _e('This Month', 'formworks' ); ?></label>
	<label class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="last_month"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="last_month" name="filters[date][preset]" {{#is filters/date/preset value="last_month"}}checked="checked"{{/is}}><?php _e('Last Month', 'formworks' ); ?></label>
	
	<label id="filter-custom-range" class="add-new-h2 formworks-filter-button {{#is filters/date/preset value="custom"}} active{{/is}}" ><input type="radio" style="display:none;" class="preset-radio" value="custom" name="filters[date][preset]" {{#is filters/date/preset value="last_month"}}checked="checked"{{/is}}><?php _e('Custom Range', 'formworks' ); ?></label>
	<div style="display: inline-block;" class="formwork-datepicker input-daterange input-group" id="formworks-range-datepicker">		
	    <input style="width: 120px; vertical-align: middle;" type="text" class="formworks-date-input" name="filters[date][start]" value="{{filters/date/start}}" />
	    <span style="display: inline-block; background: rgb(224, 224, 224) none repeat scroll 0% 0%; margin: -4px; padding: 3px 6px 6px;"><?php _e( 'to', 'formworks' ); ?></span>
	    <input style="width: 120px; vertical-align: middle;" type="text" class="formworks-date-input" name="filters[date][end]" value="{{filters/date/end}}" />
	</div>

	<label class="add-new-h2 formworks-filter-button apply-filters"><?php _e('Apply Filters', 'formworks'); ?></label>
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

<hr>
{{> core_events}}



{{> field_edits}}
{{> field_drop_off}}