<div class="formworks-main-headercaldera" style="height: 46px;">
		<h1 class="formworks-main-title">
		<a style="text-decoration: none; color: rgb(219, 68, 55);" href="<?php echo admin_url( 'admin.php?page=formworks&form=' ); ?>"><?php _e('Formworks', 'formworks') ?></a>
		/
		<?php echo $formworks['name']; ?>
		<span class="formworks-version">
			<?php echo FRMWKS_VER; ?>
		</span>		
	</h1>
	{{#unless quick_stats}}<span class="spinner" style="visibility: visible; float: none; margin: 18px 0px 0px;"></span>{{/unless}}
	{{#each quick_stats}}
		{{#if total}}
			{{#if name}}
			<span class="quick-stat" style="color: rgb(127, 127, 127); display: inline-block; border-left: 1px solid rgb(240, 240, 240); padding: 6px 6px;">
				<span class="quick-stat-name" style="display:block;">{{name}} 
					{{#if conversion}}<small>{{conversion}} {{rate_type}}</small>{{/if}}
					{{#if average_time}}<small>{{average_time}} <?php _e('Average', 'formworks'); ?></small>{{/if}}
				</span>
				{{#if conversion}}
				<div style="width: 100%; background: rgb(239, 239, 239) none repeat scroll 0% 0%; height: 4px; ">
					<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{conversion}}; height: 4px;"></div>
				</div>
				{{else}}

				<div style="width: 100%; height: 4px;"></div>
				{{/if}}	
				<span class="quick-stat-total" style="font-size: 11px;">{{#if total}}<strong>{{total}}</strong> <?php _e('across', 'formworks'); ?> {{/if}}{{#if users}}<strong>{{users}}</strong> {{n}}{{/if}}&nbsp;</span>
			</span>	
			{{/if}}
		{{/if}}
	{{/each}}
	<span class="wp-baldrick" id="formworks-field-sync" data-event="refresh" data-target="#formworks-main-canvas" data-before="frmwks_canvas_reset" data-callback="frmwks_canvas_init" data-type="json" data-request="#formworks-live-config" data-template="#main-ui-template"></span>

</div>
<div class="formworks-sub-headercaldera">
	<ul class="formworks-sub-tabs formworks-nav-tabs">
		<li class="{{#is _current_tab value="#formworks-panel-stats"}}active {{/is}}formworks-nav-tab">
			<a href="#formworks-panel-stats">
				<?php _e('Stats', 'formworks') ; ?>
			</a>
		</li>	

	</ul>
</div>

<form class="caldera-main-form has-sub-nav" id="formworks-main-form" action="?page=formworks" method="POST">
	<?php wp_nonce_field( 'formworks', 'formworks-setup' ); ?>
	<input type="hidden" value="pinned" name="id" id="formworks-id">
	<input type="hidden" name="quick_stats" id="formworks-quick-stats" value="{{#if quick_stats}}{{json quick_stats}}{{/if}}" data-live-sync="true">
	<input type="hidden" name="form_id" value="{{form_id}}">
	<input type="hidden" name="form_slug" value="{{form_slug}}">

	{{#unless quick_stats}}<span class="wp-baldrick" data-action="frmwks_get_quickstats" data-target="#formworks-quick-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>" data-autoload="true"></span>{{/unless}}

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
