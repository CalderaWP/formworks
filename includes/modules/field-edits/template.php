{{#if data/values}}
	<div style="width: 22%; min-width:260px; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
		<h4><?php echo $module['title']; ?> <small><?php echo $module['description']; ?></small></h4>
		<div class="postbox" id="pie-chart-<?php echo $module_slug; ?>" style="width: 100%; height: 250px; padding: 0px; position: relative; margin: 0px auto;"></div>
	</div>
	{{#script}}
		jQuery.plot('#pie-chart-<?php echo $module_slug; ?>', {{{json data/values}}}, {{{json data/config}}});
	{{/script}}
{{/if}}