
<div class="formworks-fixed-list" style="width: 500px;">
	<div class="formworks-config-group">
		<label><?php _e('Primary Field', 'formworks'); ?></label>
		<select class="formworks-list-select2" name="list[primary]" style="width:270px;" data-live-sync="true">
			<optgroup label="<?php echo esc_attr( __('Entry Data', 'formworks') ); ?>">
				<option value="_date" {{#is list/primary value="_date"}}selected="selected"{{else}}{{#find @root/list "_date"}}disabled="disabled"{{/find}}{{/is}}><?php _e('Entry Date', 'formworks'); ?></option>
				<option value="_entry_id" {{#is list/primary value="_entry_id"}}selected="selected"{{else}}{{#find @root/list "_entry_id"}}disabled="disabled"{{/find}}{{/is}}><?php _e('Entry ID', 'formworks'); ?></option>
			</optgroup>
			<optgroup label="<?php echo esc_attr( __('Fields', 'formworks') ); ?>">
				<?php foreach( $form['fields'] as $field ){ ?>
					<option value="<?php echo $field['slug']; ?>" {{#is list/primary value="<?php echo $field['slug']; ?>"}}selected="selected"{{else}}{{#find @root/list "<?php echo $field['slug']; ?>"}}disabled="disabled"{{/find}}{{/is}}><?php echo $field['label']; ?> [<?php echo $field['slug']; ?>]</option>
				<?php } ?>
			</optgroup>
		</select>
	</div>

	<div class="formworks-config-group">
		<label><?php _e('Context Helper', 'formworks'); ?></label>
		<select class="formworks-list-select2" name="list[context]" style="width:270px;" data-live-sync="true">
			<option></option>
			<optgroup label="<?php echo esc_attr( __('Entry Data', 'formworks') ); ?>">
				<option value="_date" {{#is list/context value="_date"}}selected="selected"{{else}}{{#find @root/list "_date"}}disabled="disabled"{{/find}}{{/is}}><?php _e('Entry Date', 'formworks'); ?></option>
				<option value="_entry_id" {{#is list/context value="_entry_id"}}selected="selected"{{else}}{{#find @root/list "_entry_id"}}disabled="disabled"{{/find}}{{/is}}><?php _e('Entry ID', 'formworks'); ?></option>
			</optgroup>
			<optgroup label="<?php echo esc_attr( __('Fields', 'formworks') ); ?>">
				<?php foreach( $form['fields'] as $field ){ ?>
					<option value="<?php echo $field['slug']; ?>" {{#is list/context value="<?php echo $field['slug']; ?>"}}selected="selected"{{else}}{{#find @root/list "<?php echo $field['slug']; ?>"}}disabled="disabled"{{/find}}{{/is}}><?php echo $field['label']; ?> [<?php echo $field['slug']; ?>]</option>
				<?php } ?>
			</optgroup>
		</select>
	</div>

	<div class="formworks-config-group">
		<label><?php _e('Subline', 'formworks'); ?></label>
		<select class="formworks-list-select2" name="list[subline]" style="width:270px;" data-live-sync="true">
			<option></option>
			<optgroup label="<?php echo esc_attr( __('Entry Data', 'formworks') ); ?>">
				<option value="_date" {{#is list/subline value="_date"}}selected="selected"{{else}}{{#find @root/list "_date"}}disabled="disabled"{{/find}}{{/is}}><?php _e('Entry Date', 'formworks'); ?></option>
				<option value="_entry_id" {{#is list/subline value="_entry_id"}}selected="selected"{{else}}{{#find @root/list "_entry_id"}}disabled="disabled"{{/find}}{{/is}}><?php _e('Entry ID', 'formworks'); ?></option>
			</optgroup>
			<optgroup label="<?php echo esc_attr( __('Fields', 'formworks') ); ?>">
				<?php foreach( $form['fields'] as $field ){ ?>
					<option value="<?php echo $field['slug']; ?>" {{#is list/subline value="<?php echo $field['slug']; ?>"}}selected="selected"{{else}}{{#find @root/list "<?php echo $field['slug']; ?>"}}disabled="disabled"{{/find}}{{/is}}><?php echo $field['label']; ?> [<?php echo $field['slug']; ?>]</option>
				<?php } ?>
			</optgroup>
		</select>
	</div>


	<div class="formworks-config-group">
		<label><?php _e('Paginators', 'formworks'); ?></label>
		<button type="button" class="button wp-baldrick"
				data-add-node="paginator"
				data-node-default='{ "type" : "standard", "position" : "top" }'

			><?php _e( 'Add Paginator', 'formworks' ); ?></button>
	</div>

	{{#each paginator}}
	<div id="paginator-line-{{_id}}">
		<div class="formworks-config-group">
			{{:node_point}}
			<label></label>
			<select name="{{:name}}[type]" data-live-sync="true">
				<option value="standard" {{#is type value="standard"}}selected="selected"{{/is}}>Standard</option>
				<option value="alphabetical" {{#is type value="alphabetical"}}selected="selected"{{/is}}>Alphabetical</option>
				<option value="date" {{#is type value="date"}}selected="selected"{{/is}}>Date</option>
			</select>
			<select name="{{:name}}[position]" data-live-sync="true">
				<option value="top" {{#is position value="top"}}selected="selected"{{/is}}>Top</option>
				<option value="bottom" {{#is position value="bottom"}}selected="selected"{{/is}}>Bottom</option>
			</select>
			<button type="button" class="button" data-remove-element="#paginator-line-{{_id}}" style="margin: 1px; padding: 3px 6px 0px 5px;"><span class="dashicons dashicons-no"></span></button>
		</div>
	</div>
	{{/each}}



	<div class="formworks-config-group">
		<label><?php _e('Filters', 'formworks'); ?></label>
		<label style="width: 153px;"><input type="checkbox" name="general_search" value="1" {{#if general_search}}checked="checked"{{/if}} data-live-sync="true"> <?php _e('General Search', 'formworks'); ?></label>
		<button type="button" class="button wp-baldrick"
				data-add-node="filter"
				data-node-default='{ "size" : "50%" }'
		><?php _e( 'Add Filter', 'formworks' ); ?></button>
	</div>
	{{#if filter}}
	<div class="formworks-config-group">
		<label></label>
		<label style="width: 153px;"><input type="checkbox" name="toggle_filters" value="1" {{#if toggle_filters}}checked="checked"{{/if}} data-live-sync="true"> <?php _e('Filter Toggle', 'formworks'); ?></label>
	</div>
	{{/if}}

	{{#each filter}}
	<div id="filter-line-{{_id}}">
		<div class="formworks-config-group">
			{{:node_point}}
			<label></label>
			<select name="{{:name}}[field]" data-live-sync="true" style="width: 175px;">
				<option></option>
				<optgroup label="<?php echo esc_attr( __('Fields', 'formworks') ); ?>">
				<?php foreach( $form['fields'] as $field ){ ?>
					<option value="<?php echo $field['slug']; ?>" {{#is field value="<?php echo $field['slug']; ?>"}}selected="selected"{{else}}{{#find @root/filter "<?php echo $field['slug']; ?>"}}disabled="disabled"{{/find}}{{/is}}><?php echo $field['label']; ?> [<?php echo $field['slug']; ?>]</option>
				<?php } ?>
				</optgroup>
			</select>
			<select name="{{:name}}[size]" data-live-sync="true" style="width: 55px;">
				<option value="20%" {{#is size value="20%"}}selected="selected"{{/is}}>20%</option>
				<option value="25%" {{#is size value="25%"}}selected="selected"{{/is}}>25%</option>
				<option value="33.33%" {{#is size value="33.33%"}}selected="selected"{{/is}}>30%</option>
				<option value="50%" {{#is size value="50%"}}selected="selected"{{/is}}>50%</option>
				<option value="75%" {{#is size value="75%"}}selected="selected"{{/is}}>75%</option>
				<option value="100%" {{#is size value="100%"}}selected="selected"{{/is}}>100%</option>
			</select>
			<button type="button" class="button" data-remove-element="#filter-line-{{_id}}" style="margin: 1px; padding: 3px 6px 0px 5px;"><span class="dashicons dashicons-no"></span></button>
		</div>
	</div>
	{{/each}}




</div>

<div class="formworks-fixed-list">
	<input type="hidden" name="data" value="{{json data}}" id="new-paginator-base">
	<div class="postbox">
			{{#if filter}}
				{{#if toggle_filters}}
				<span class="dashicons dashicons-filter" style="float: right; cursor: pointer; padding: 8px 8px 0px 4px; color: rgb(143, 143, 143);" data-toggle="#filters-panel"></span>
				{{/if}}
			{{/if}}
			{{#if general_search}}
			<input type="search" name="_test_search" placeholder="<?php _e( 'Search', 'formworks' ); ?>" style="float: right; margin: 4px 4px 0px;">
			{{/if}}
			<h3 style="font-size: 14px;line-height: 1.4;margin: 0;padding: 8px 9pt; border-bottom: 1px solid #eee;" >
				<span><?php _e( 'Entries', 'formworks' ); ?></span>
			</h3>
			<div style="height:auto; overflow:auto;">
				{{#if filter}}
					<div id="filters-panel">
					{{#each filter}}
						
						<?php foreach( $form['fields'] as $field ){ ?>

							{{#is field value="<?php echo $field['slug']; ?>"}}

								<div style="margin-bottom: -1px;padding: 6px; border-bottom: 1px solid rgb(239, 239, 239); width: {{#if size}}{{size}}{{else}}50%{{/if}}; float: left; box-sizing: padding-box;">
									<?php if( $field['type'] == 'dropdown' || $field['type'] == 'radio' || $field['type'] == 'toggle_switch' || $field['type'] == 'checkbox' ){ ?>
										<select class="select2-filter" style="width:100%;" multiple="multiple" placeholder="<?php echo esc_attr( $field['label'] ); ?>">
											<option></option>
											<?php foreach( $field['config']['option'] as $option ){ ?>
												<option><?php echo $option['label']; ?></option>
											<?php } ?>
										</select>
									<?php }else{ ?>
										<input type="search" placeholder="<?php echo esc_attr( $field['label'] ); ?>" style="width:100%;">
									<?php } ?>
								</div>
							{{/is}}

						<?php } ?>

					{{/each}}
					<div style="border-top:1px solid #efefef;clear:both;"></div>				
					</div>
				{{/if}}
				{{#each paginator}}
					{{#is position value="top"}}
						<div style="clear: both; height: 35px; border-bottom: 1px solid rgb(239, 239, 239); margin: 6px 0px;">							

							{{#is type value="standard"}}
							<?php
								/**
								 * Include standard paginator example template
								 */
								include FRMWKS_PATH . 'includes/templates/paginator-standard-template.php';
							?>
							{{/is}}
							{{#is type value="alphabetical"}}
							<?php
								/**
								 * Include alphabetical paginator example template
								 */
								include FRMWKS_PATH . 'includes/templates/paginator-alphabetical-template.php';
							?>
							{{/is}}
							{{#is type value="date"}}
							<?php
								/**
								 * Include date paginator example template
								 */
								include FRMWKS_PATH . 'includes/templates/paginator-date-template.php';
							?>
							{{/is}}

						</div>
					{{/is}}
				{{/each}}
				<table class="striped" style="width:100%;">
					<tbody>
					{{#each data/entries}}
						<tr>
							<td style="{{#is @root/_open_entries value=@key}}background: #db4437 none repeat scroll 0% 0%; color: rgb(255, 255, 255);{{/is}}">
								<label style="padding: 11px;text-transform: capitalize;display: block;">
								
								
								{{#is @root/list/primary value="_date"}}
									{{_date}} 
								{{/is}}
								{{#is @root/list/primary value="_entry_id"}}
									{{_entry_id}} 
								{{/is}}
								
								{{#find data @root/list/primary}}
									{{this}}
								{{/find}}
							
								{{#if @root/list/context}}
									<small style="color: rgb(143, 143, 143);">
										{{#is @root/list/context value="_date"}}
											{{_date}} 
										{{/is}}
										{{#is @root/list/context value="_entry_id"}}
											{{_entry_id}} 
										{{/is}}
										
										{{#find data @root/list/context}}
											{{this}}
										{{/find}}
									</small>
								{{/if}}
								{{#if @root/list/subline}}
									<p class="description"  style="font-size: 0.9em;">
										{{#is @root/list/subline value="_date"}}
											{{_date}} 
										{{/is}}
										{{#is @root/list/subline value="_entry_id"}}
											{{_entry_id}} 
										{{/is}}
										
										{{#find data @root/list/subline}}
											{{this}}
										{{/find}}
									</p>
								{{/if}}
								</label>
							</td>
						</tr>
					{{/each}}
					</tbody>
				</table>
				{{#each paginator}}
					{{#is position value="bottom"}}
						<div style="clear: both; height: 35px; border-top: 1px solid rgb(239, 239, 239); margin: 0 0 0; padding-top: 6px;">
							

							{{#is type value="standard"}}
							<?php
								/**
								 * Include standard paginator example template
								 */
								include FRMWKS_PATH . 'includes/templates/paginator-standard-template.php';
							?>
							{{/is}}
							{{#is type value="alphabetical"}}
							<?php
								/**
								 * Include alphabetical paginator example template
								 */
								include FRMWKS_PATH . 'includes/templates/paginator-alphabetical-template.php';
							?>
							{{/is}}
							{{#is type value="date"}}
							<?php
								/**
								 * Include date paginator example template
								 */
								include FRMWKS_PATH . 'includes/templates/paginator-date-template.php';
							?>
							{{/is}}

							
						</div>
					{{/is}}
				{{/each}}				
			</div>
		</div>
</div>

{{#script}}
	jQuery( function($){
		$(".select2-filter").select2({allowClear: true});
	});
{{/script}}