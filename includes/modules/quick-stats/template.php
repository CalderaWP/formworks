
{{#each this}}
	{{#if total}}
		{{#if name}}
		<span class="quick-stat" style="display: inline-block; {{#unless @first}} border-left: 1px solid #dfdfdf;{{/unless}} padding: 6px 6px;">
			<span class="quick-stat-name" style="display:block;">{{name}} 
				{{#if conversion}}<small>{{conversion}} {{rate_type}}</small>{{/if}}
				{{#if average_time}}<small>{{average_time}} <?php _e('Average', 'formworks'); ?></small>{{/if}}
			</span>
			{{#if conversion}}
			<div style="width: 100%; background: #dadada none repeat scroll 0% 0%; height: 4px; ">
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

