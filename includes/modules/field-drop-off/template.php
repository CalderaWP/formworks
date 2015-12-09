{{#if values}}	
		<h4>
			<?php esc_html_e( $module[ 'title' ] ); ?> <small><?php esc_html_e( $module[ 'description' ] ); ?></small>
		</h4>
		<div class="postbox" id="pie-chart-<?php echo $module_slug; ?>" style="width: 100%; height: 250px; padding: 0px; position: relative; margin: 0px auto;"></div>
		{{#script}}
			jQuery.plot('#pie-chart-<?php echo $module_slug; ?>', {{{json values}}}, {{{json config}}});
		{{/script}}
{{/if}}
