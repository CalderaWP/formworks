	<input type="hidden" name="forms" value="{{#if forms}}{{json forms}}{{/if}}">
	{{#if forms}}

		{{#each forms}}
		<div class="formworks-config-group">
			<label>
				{{name}}
			</label>
			<div style="display: inline-block; margin-top: 6px;">
				{{#each forms}}

					<label style="display: block; width: 300px;"><input type="checkbox" name="track_form[{{@key}}]" value="{{this}}" {{#find @root/track_form @key}}checked="checked"{{/find}}> {{this}}</label>
				{{/each}}
			</div>
		</div>
		{{/each}}

	{{/if}}

