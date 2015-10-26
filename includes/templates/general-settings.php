	<input type="hidden" name="forms" value="{{#if forms}}{{json forms}}{{/if}}">
	{{#if forms}}

		{{#each forms}}
		<div class="formworks-config-group">
			<label>
				{{name}}
			</label>
			<div style="display: inline-block; margin-top: 6px;">
				{{#each forms}}
					
					<label style="display: block; width: 300px;"><input type="checkbox" name="track_form[{{../slug}}][{{@key}}]" value="{{this}}" 
						{{#find @root/track_form ../slug}}{{#find this @key}}checked="checked"{{/find}}{{/find}}
					> {{this}}</label>
					
				{{/each}}
			</div>
		</div>
		{{/each}}

	{{/if}}
