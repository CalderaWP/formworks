<?php
/**
 * @author    David Cramer <david@digilab.co.za>
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer <david@digilab.co.za>
 */
	
	if( !empty( $formworks['list']['primary'] ) ){
		if( $formworks['list']['primary'] != '_date' && $formworks['list']['primary'] != '_entry_id') {
			$formworks['list']['primary'] = 'data/' . $formworks['list']['primary'];
		}
	}
	if( !empty( $formworks['list']['context'] ) ){
		if( $formworks['list']['context'] != '_date' && $formworks['list']['context'] != '_entry_id') {
			$formworks['list']['context'] = 'data/' . $formworks['list']['context'];
		}
	}
	if( !empty( $formworks['list']['subline'] ) ){
		if( $formworks['list']['subline'] != '_date' && $formworks['list']['subline'] != '_entry_id') {
			$formworks['list']['subline'] = 'data/' . $formworks['list']['subline'];
		}
	}
?>

<input type="hidden" name="paginator" value="{{json paginator}}">
<input type="hidden" id="formworks-entry-store" name="data" value="{{#if data}}{{json data}}{{/if}}" data-live-sync="true">

{{#unless data}}
<input type="hidden" id="formworks-limit-store" name="limit" value="{{limit}}" class="wp-baldrick"
	data-action="frmwks_get_entries"
	data-page="1"
	data-autoload="true"
	data-form="<?php echo $formworks['form']; ?>"
	data-formtype="CF"
	data-target="#formworks-entry-store"					
>{{/unless}}
<input type="hidden" name="_open_entries" value="{{_open_entries}}">
<div class="formworks-fixed-list">
	<div class="postbox formworks-loading">
			<h3 style="font-size: 14px;line-height: 1.4;margin: 0;padding: 8px 9pt; border-bottom: 1px solid #eee;" >
				<span><?php _e( 'Entries', 'formworks' ); ?></span>
			</h3>
			<div style="height:auto; overflow:auto;">
				{{#each paginator}}
					{{#is position value="top"}}
						{{#is type value="standard"}}		
							<div class="formworks-pagination pagination-top"></div>
						{{/is}}
					{{/is}}
				{{/each}}
				<table class="striped" style="width:100%;">
					<tbody>
					{{#each data/entries}}
						<tr>
							<td style="{{#is @root/_open_entries value=@key}}background: #db4437 none repeat scroll 0% 0%; color: rgb(255, 255, 255);{{/is}}">
								<label style="padding: 11px;text-transform: capitalize;display: block;">
								<input type="checkbox" name="_open_entries" value="{{@key}}" data-live-sync="true" style="display:none;">						
									<?php if(!empty( $formworks['list']['primary'] ) ){
										echo '{{' . $formworks['list']['primary'] . '}}';
									} ?>
									<?php if(!empty( $formworks['list']['context'] ) ){
										echo '<small style="{{#is @root/_open_entries value=@key}}color: rgb(255, 255, 255); opacity: 0.6;{{else}}color: rgb(143, 143, 143);{{/is}}">{{' . $formworks['list']['context'] . '}}</small>';
									} ?>
									<?php if(!empty( $formworks['list']['subline'] ) ){
										echo '<p class="description"  style="{{#is @root/_open_entries value=@key}}color: rgb(255, 255, 255); opacity: 0.6;{{/is}}font-size: 0.9em;">{{' . $formworks['list']['subline'] . '}}</p>';
									} ?>
								</label>
							</td>
						</tr>						
					{{/each}}
					</tbody>
				</table>
				{{#each paginator}}
					{{#is position value="bottom"}}
						{{#is type value="standard"}}		
							<div class="formworks-pagination pagination-bottom"></div>
						{{/is}}
					{{/is}}
				{{/each}}

				<input type="number" id="formworks-limit-store" name="limit" value="{{limit}}" class="wp-baldrick" style="width:60px;"
					data-action="frmwks_get_entries"
					data-page="{{data/current_page}}"
					data-event="sync change"
					data-form="<?php echo $formworks['form']; ?>"
					data-formtype="CF"
					data-target="#formworks-entry-store"					
				>

			</div>			

		</div>
</div>
<div class="formworks-fixed-center">
	{{#find @root/data/entries @root/_open_entries}}
		
		{{#if user}}
			{{#if full_entry/user/avatar}}
				{{{full_entry/user/avatar}}}
			{{/if}}
			<h3 style="border-bottom: 1px solid rgb(207, 207, 207); margin: 8px 0px; padding: 0px 0px 14px;">{{user/name}} <small style="color: rgb(175, 175, 175); font-weight: normal; font-size: 0.7em;">{{_date}}</small></h3>
			
			
		{{else}}
			{{#if full_entry/user/avatar}}
				{{{full_entry/user/avatar}}}
			{{/if}}		
			<h3 style="border-bottom: 1px solid rgb(207, 207, 207); margin: 8px 0px; padding: 0px 0px 14px;">{{_date}}</h3>
		{{/if}}



		{{#unless full_entry}}
			<input type="hidden" id="full_entry_{{@root/_open_entries}}"

			class="wp-baldrick"
			data-action="frmwks_get_entry"
			data-target="#full_entry_{{@root/_open_entries}}"
			data-live-sync="true"
			data-autoload="true"
			data-entry="{{_entry_id}}"
			data-form="<?php echo $formworks['form']; ?>"
			data-formtype="CF"
			name="data[entries][{{@root/_open_entries}}][full_entry]" value="">
			<span class="spinner" style="visibility:visible; float:left;"></span>
		{{else}}			
			{{#if meta}}
			{{#each meta}}
			<div id="meta-{{@key}}" data-tab="{{name}}" class="tab-detail-panel">
			<h4>{{name}}</h4>
			<hr>
			{{#unless template}}
				{{#each data}}
					{{#if title}}
						<h4>{{title}}</h4>
					{{/if}}
					{{#each entry}}
						<div class="entry-line">
							<label>{{meta_key}}</label>
							<div>{{{meta_value}}}&nbsp;</div>
						</div>
					{{/each}}
				{{/each}}
			{{/unless}}
			<?php do_action('caldera_forms_entry_meta_templates'); ?>
			</div>
			{{/each}}
			{{/if}}

			<div style="overflow: auto; height: 100%;">
				{{#each full_entry/data}}
				<div class="entry-line" style="border-bottom: 1px solid #e8e8e8;margin-bottom: 4px;">
					<label style="clear: left;float: left;font-weight: bold;margin-bottom: 3px;margin-right: 8px;min-width: 270px;text-transform: capitalize">{{label}}</label>
					<div style="display: inline-block;margin-bottom: 4px;min-width: 295px;">{{{view}}}&nbsp;</div>
				</div>
				{{/each}}
			</div>
		{{/unless}}		


	{{/find}}

</div>
{{#if data}}
{{#script}}
	//<script>
	jQuery( function( $ ){
		$('.formworks-pagination').bootpag({
			total: {{data/pages}},          // total pages
			page: {{data/current_page}},            // default page
			maxVisible: 10,     // visible pagination
			leaps: true,         // next/prev leaps through maxVisible
			firstLastUse: true
		}).on("page", function(event, num){
				$('[data-lp="' + num + '"] > a').html('<a class="spinner" style="float: none; visibility: visible; margin: -3px;"></a>').parent().addClass('load');
				$('#formworks-page-store').val( num );
				$('#formworks-limit-store').data('page', num).trigger('sync');
				console.log( event );
			// ... after content load -> change total to 10
			//$(this).bootpag({total: 10, maxVisible: 10});
		});
	});

{{/script}}
{{/if}}