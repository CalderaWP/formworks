	<input type="hidden" name="forms" value="{{#if forms}}{{json forms}}{{/if}}">
	{{#if forms}}
		<table class="widefat fixed striped formworks-admin">
			<thead>
				<tr>
					<th style="width: 220px;"><?php esc_html_e('Form', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php esc_html_e('Loads', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php esc_html_e('Views', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php esc_html_e('Engagements', 'formworks'); ?></th>
					<th style="width: 90px;text-align: center;"><?php esc_html_e('Submissions', 'formworks'); ?></th>
					<th style="width: 100px;text-align: right;"><?php esc_html_e('Action', 'formworks'); ?></th>
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
						sparkHeight="61px"
						sparkWidth="100%"
						sparkFillColor="false"
						sparkBarWidth="18"
						sparkBarSpacing="1"
						sparkZeroColor="transparent"
						sparkBarColor="#db4437"><!--{{activity}}--></div>
						<span style="position:absolute;top: 12px;"><a href="<?php echo admin_url( 'admin.php?page=formworks&form=' ); ?>{{../slug}}_{{@key}}">{{name}}</a>
						<small style="opacity: 0.6; display: block;">{{../name}}</small>
						</span>

						</td>
					<td style="text-align: center;">
					{{#with loaded}}
						{{#if name}}
						<span class="quick-stat" style="padding: 0;">
							<span class="quick-stat-name" style="display:block;">
								{{#if conversion}}<small>{{conversion}} {{rate_type}}</small>{{/if}}
							</span>
							{{#if conversion}}
							<div style="width: 100%; background: #dadada none repeat scroll 0% 0%; height: 4px; ">
								<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{conversion}}; height: 4px;"></div>
							</div>
							{{else}}

							<div style="width: 100%; height: 4px;"></div>
							{{/if}}	
							<span class="quick-stat-total" style="font-size: 11px;">{{#if total}}<strong>{{total}}</strong> <?php esc_html_e('across', 'formworks'); ?> {{/if}}{{#if users}}<strong>{{users}}</strong> {{n}}{{/if}}&nbsp;</span>
						</span>	
						{{/if}}
					{{/with}}
					</td>
					<td style="text-align: center;">
					{{#with view}}
						{{#if name}}
						<span class="quick-stat" style="padding: 0;">
							<span class="quick-stat-name" style="display:block;">
								{{#if conversion}}<small>{{conversion}} {{rate_type}}</small>{{/if}}
							</span>
							{{#if conversion}}
							<div style="width: 100%; background: #dadada none repeat scroll 0% 0%; height: 4px; ">
								<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{conversion}}; height: 4px;"></div>
							</div>
							{{else}}

							<div style="width: 100%; height: 4px;"></div>
							{{/if}}	
							<span class="quick-stat-total" style="font-size: 11px;">{{#if total}}<strong>{{total}}</strong> <?php esc_html_e('across', 'formworks'); ?> {{/if}}{{#if users}}<strong>{{users}}</strong> {{n}}{{/if}}&nbsp;</span>
						</span>	
						{{/if}}					
					{{/with}}
					</td>
					<td style="text-align: center;">
					{{#with engage}}
						{{#if name}}
						<span class="quick-stat" style="padding: 0;">
							<span class="quick-stat-name" style="display:block;">
								{{#if conversion}}<small>{{conversion}} {{rate_type}}</small>{{/if}}
							</span>
							{{#if conversion}}
							<div style="width: 100%; background: #dadada none repeat scroll 0% 0%; height: 4px; ">
								<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{conversion}}; height: 4px;"></div>
							</div>
							{{else}}

							<div style="width: 100%; height: 4px;"></div>
							{{/if}}	
							<span class="quick-stat-total" style="font-size: 11px;">{{#if total}}<strong>{{total}}</strong> <?php esc_html_e('across', 'formworks'); ?> {{/if}}{{#if users}}<strong>{{users}}</strong> {{n}}{{/if}}&nbsp;</span>
						</span>	
						{{/if}}					
					{{/with}}
					</td>
					<td style="text-align: center;">
					{{#with submission}}
						{{#if name}}
						<span class="quick-stat" style="padding: 0;">
							<span class="quick-stat-name" style="display:block;">
								<span class="quick-stat-total" style="font-size: 11px;">{{#if total}}<strong>{{total}}</strong> <?php esc_html_e('across', 'formworks'); ?> {{/if}}{{#if users}}<strong>{{users}}</strong> {{n}}{{/if}}&nbsp;</span>
							</span>
							{{#if conversion}}
							<div style="width: 100%; background: #dadada none repeat scroll 0% 0%; height: 4px; ">
								<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{conversion}}; height: 4px;"></div>
							</div>
							{{else}}

							<div style="width: 100%; height: 4px;"></div>
							{{/if}}	
							{{#if total}}
								{{#if conversion}}<small>{{conversion}} {{rate_type}}</small>{{/if}}
								{{#if average_time}}{{average_time}} <?php esc_html_e('Average', 'formworks'); ?>{{/if}}
							{{else}}
								0
							{{/if}}
							
						</span>	
						{{/if}}					
					{{/with}}
					</td>
					<td style="width: 100px;text-align: right;"><a href="<?php echo admin_url( 'admin.php?page=formworks&form=' ); ?>{{../slug}}_{{@key}}"><?php esc_html_e('View Report', 'formworks'); ?></a></td>
				</tr>
				{{/each}}

			{{/each}}
			</tbody>
		</table>
	{{/if}}

{{#script}}
jQuery('.sparklines').sparkline('html', { enableTagOptions: true });
{{/script}}
