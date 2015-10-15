
<div style="white-space: nowrap; overflow:auto; ">
	<div class="formworks-group-wrapper" style="width:auto;">
	{{#each columns}}
	<div id="column-{{_id}}" style="height: 200px; display: block; float:left; width: auto; min-width:95px;position:relative;" class="postbox">
		<div >
			{{#unless field}}
			<select name="{{:name}}[field]" data-live-sync="true" style="max-width: 100px; width: auto; ">
			<option></option>
			<optgroup label="<?php _e('Entry Details', 'formworks'); ?>">
				<option value="_date"><?php _e('Entry Date', 'formworks'); ?></option>
				<option value="_entry_id"><?php _e('Entry ID', 'formworks'); ?></option>
			</optgroup>
			<optgroup label="<?php _e('Fields', 'formworks'); ?>">
				<?php
				 foreach( $form['fields'] as $field ){
				?>
				<option value="<?php echo $field['slug']; ?>"{{#is field value="<?php echo $field['slug']; ?>"}}selected="selected"{{/is}}><?php echo $field['label']; ?></option>
				<?php } ?>
			</optgroup>
			<optgroup label="<?php _e('Entry Management', 'formworks'); ?>">
				<option value="_edit"><?php _e('Edit', 'formworks'); ?></option>
				<option value="_View"><?php _e('View', 'formworks'); ?></option>
				<option value="_delete"><?php _e('Delete', 'formworks'); ?></option>
			</optgroup>			
			</select>
			{{else}}
			<span class="dashicons dashicons-menu sortable-item" style="cursor:move;"></span>
			<span style="display:inline-block;padding:6px 12px;">{{field}}</span>
			<input type="hidden" name="{{:name}}[field]" value="{{field}}">
			<div style="padding: 12px 6px;">
				<label><input type="checkbox" name="{{:name}}[sortable]" value="1" {{#if sortable}}checked="checked"{{/if}}> <?php _e('Sortable', 'formworks' ); ?></label>
			</div>

			<h4 style="margin:6px;padding:0;"><?php _e('Breakpoints', 'formworks' ); ?></h4>
			<div style="padding: 1px 6px 0;">
				<label><input type="checkbox" name="{{:name}}[breakpoints][xs]" value="1" {{#if breakpoints/xs}}checked="checked"{{/if}}> <?php _e('Extra Small', 'formworks' ); ?></label>
			</div>
			<div style="padding: 1px 6px 0;">
				<label><input type="checkbox" name="{{:name}}[breakpoints][sm]" value="1" {{#if breakpoints/sm}}checked="checked"{{/if}}> <?php _e('Small', 'formworks' ); ?></label>
			</div>
			<div style="padding: 1px 6px 0;">
				<label><input type="checkbox" name="{{:name}}[breakpoints][md]" value="1" {{#if breakpoints/md}}checked="checked"{{/if}}> <?php _e('Medium', 'formworks' ); ?></label>
			</div>
			<div style="padding: 1px 6px 0;">
				<label><input type="checkbox" name="{{:name}}[breakpoints][lg]" value="1" {{#if breakpoints/lg}}checked="checked"{{/if}}> <?php _e('Large', 'formworks' ); ?></label>
			</div>

			{{/unless}}
			{{:node_point}}

		</div>
		<button type="button" class="button button-small" style="border-radius:0;position: absolute; bottom: 0px; left:0px;width: 100%;" data-remove-element="#column-{{_id}}"><?php _e('Remove Column', 'formworks' ); ?></button>
	</div>
	{{/each}}
	</div>
	<button style="display:inline" type="button" class="button wp-baldrick" data-add-node="columns"><?php _e('Add Column', 'formworks' ); ?></button>
</div>