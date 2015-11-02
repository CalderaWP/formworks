	<input type="hidden" name="forms" value="{{#if forms}}{{json forms}}{{/if}}">
	{{#if forms}}
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 220px;"><?php _e('Form', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php _e('Loads', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php _e('Views', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php _e('Engagements', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php _e('Submissions', 'formworks'); ?></th>
					<th style="width: 100px;text-align: right;"><?php _e('Conversion', 'formworks'); ?></th>
					<th style="width: 100px;text-align: right;"><?php _e('Action', 'formworks'); ?></th>
				</tr>
			</thead>
			<tbody>
			{{#each forms}}

				{{#each forms}}
				<tr>
					<td style="position:relative;"><div class="sparklines" 
						style="opacity: 0.08; margin: -8px -10px; width: 100%; overflow: hidden;"
						sparkType="bar"
						sparkSpotColor="#db4437"
						sparkDisableInteraction="true"
						sparkSpotRadius=""
						sparkHighlightLineColor="#ccc"
						sparkHeight="36px"
						sparkWidth="100%"
						sparkFillColor="false"
						sparkBarWidth="18"
						sparkBarSpacing="1"
						sparkZeroColor="transparent"
						sparkBarColor="#db4437"><!--{{activity}}--></div><span style="position:absolute;top: 7px;"><a href="<?php echo admin_url( 'admin.php?page=formworks&form=' ); ?>{{../slug}}_{{@key}}">{{name}}</a> <small style="opacity:0.5;">{{../name}}</small></span></td>				
					<td style="text-align: center;">{{#if loaded}}{{loaded}}{{else}}{{/if}}</td>
					<td style="text-align: center;">{{#if view}}{{view}}{{else}}{{/if}}</td>
					<td style="text-align: center;">{{#if engage}}{{engage}}{{else}}{{/if}}</td>
					<td style="text-align: center;">{{#if submission}}{{submission}}{{else}}{{/if}}</td>
					<td style="text-align: right; box-shadow: {{conversion}}px 0px 0px rgba(137, 180, 32, 0.2) inset; background: rgba(0, 0, 0, 0.02) none repeat scroll 0% 0%;">{{conversion}}%</td>
					<td style="width: 100px;text-align: right;"><a href="<?php echo admin_url( 'admin.php?page=formworks&form=' ); ?>{{../slug}}_{{@key}}"><?php _e('View Report', 'formworks'); ?></a></td>
				</tr>
				{{/each}}

			{{/each}}
			</tbody>
		</table>
	{{/if}}

{{#script}}
jQuery('.sparklines').sparkline('html', { enableTagOptions: true });
{{/script}}