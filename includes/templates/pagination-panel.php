

			<div class="formworks-config-group">
				<label for="formworks-template-limit">
					<?php _e( 'Rows per page', 'formworks' ); ?>
				</label>
				<input id="formworks-template-limit" type="number" name="pagination_style[limit]" value="{{pagination_style/limit}}" style="width:100px;">
			</div>
			<div class="formworks-config-group">
				<label for="formworks-template-size">
					<?php _e( 'Number of page links', 'formworks' ); ?>
				</label>
				<input id="formworks-template-size" type="number" name="pagination_style[size]" value="{{pagination_style/size}}" style="width:100px;">
			</div>
			<div class="formworks-config-group">
				<label for="formworks-template-position">
					<?php _e( 'Position', 'formworks' ); ?>
				</label>
				<select id="formworks-template-position" name="pagination_style[position]" value="{{pagination_style/position}}">
				<option value="left" {{#is pagination_style/position value="left"}}selected="selected"{{/is}}><?php _e('Left', 'formworks'); ?></option>
				<option value="center" {{#is pagination_style/position value="center"}}selected="selected"{{/is}}><?php _e('Center', 'formworks'); ?></option>				
				<option value="right" {{#is pagination_style/position value="right"}}selected="selected"{{/is}}><?php _e('Right', 'formworks'); ?></option>
				</select>
			</div>
			<div class="formworks-config-group">
				<label for="formworks-template-text">
					<?php _e( 'Text Color', 'formworks' ); ?>
				</label>
				<input id="formworks-template-text" type="text" class="color-field" data-target=".paginate-block" data-style="color" name="pagination_style[text]" value="{{pagination_style/text}}">
			</div>
			<div class="formworks-config-group">
				<label for="formworks-template-background">
					<?php _e( 'Background Color', 'formworks' ); ?>
				</label>
				<input id="formworks-template-background" type="text" class="color-field" data-target=".paginate-block" data-style="background" name="pagination_style[background]" value="{{pagination_style/background}}">
			</div>
			<div class="formworks-config-group">
				<label for="formworks-template-active_text">
					<?php _e( 'Active Text Color', 'formworks' ); ?>
				</label>
				<input id="formworks-template-active_text" type="text" class="color-field" data-target=".paginate-active" data-style="color" name="pagination_style[active_text]" value="{{pagination_style/active_text}}">
			</div>
			<div class="formworks-config-group">
				<label for="formworks-template-active">
					<?php _e( 'Active Background', 'formworks' ); ?>
				</label>
				<input id="formworks-template-active" type="text" class="color-field" data-target=".paginate-active" data-style="background" name="pagination_style[active]" value="{{pagination_style/active}}">
			</div>

			<style type="text/css">

					.navigation {
						text-align: {{pagination_style/align}};
					}
					.navigation li a,.navigation li.active a,.navigation li.disabled {
						color: {{pagination_style/text}};text-decoration:none;
					}
					.navigation li {
						display: inline;margin:0;
					}
					.navigation li a,.navigation li a:hover,.navigation li.active a,.navigation li.disabled {
						display:inline;background-color: {{pagination_style/background}};border-radius: 3px;cursor: pointer;padding: 12px;padding: 0.25rem 0.62rem;
					}
					.navigation li a:hover,.navigation li.active a {
						background-color: {{pagination_style/active}}; color:{{pagination_style/active_text}};
					}

			</style>

		<div class="formworks-config-group">
			<label for="formworks-template-preview">
				<?php _e( 'Pagination Preview', 'formworks' ); ?>
			</label>
    
			<div class="navigation" style="display: inline-block; margin: 6px 0px 12px;">
				<ul style="display: inline;">
					<li><a class="paginate-block">«</a></li>
					<li><a class="paginate-block">‹</a></li>
					<li><a class="paginate-block">...</a></li>
					<li><a class="paginate-block">1</a></li>
					<li class="active"><a class="paginate-active">2</a></li>
					<li><a class="paginate-block">3</a></li>
					<li><a class="paginate-block">4</a></li>
					<li><a class="paginate-block">...</a></li>
					<li><a class="paginate-block">›</a></li>
					<li><a class="paginate-block">»</a></li>

				</ul>
			</div>


		</div>