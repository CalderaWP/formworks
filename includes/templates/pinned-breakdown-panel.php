<?php
/**
 * Breakdown Panel template for Stats
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */


?>

	<div style="width: 33%; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
		<h4><?php _e('Conversion Rates', 'formworks'); ?></h4>
		{{#each main_stats/datasets}}
			{{#if is_conversion}}
			<div class="formworks-config-group">
				<strong style="float: left; margin: -3px 0px 0px; width:120px;">{{label}}</strong>
				<span style="float: left; margin: -3px 0px 0px; width:80px;">{{rate}}%</span>
				<span class="quick-stat" style="color: rgb(127, 127, 127); margin-bottom: 12px; display: block; margin-left: 180px;">
					<div style="width: 100%; background: rgb(207, 207, 207) none repeat scroll 0% 0%; height: 14px; ">
						<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{rate}}%; height: 14px;"></div>
					</div>
					<p class="description">{{description}}</p>
				</span>
			</div>
			{{/if}}
		{{/each}}
	</div>
	<div style="width: 33%; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
		<h4><?php _e('Engagement Rates', 'formworks'); ?></h4>
		{{#each main_stats/datasets}}
			{{#if is_engage}}
			<div class="formworks-config-group">
				<strong style="float: left; margin: -3px 0px 0px; width:120px;">{{label}}</strong>
				<span style="float: left; margin: -3px 0px 0px; width:80px;">{{rate}}%</span>
				<span class="quick-stat" style="color: rgb(127, 127, 127); margin-bottom: 12px; display: block; margin-left: 180px;">
					<div style="width: 100%; background: rgb(207, 207, 207) none repeat scroll 0% 0%; height: 14px; ">
						<div style="background: rgb(219, 68, 55) none repeat scroll 0% 0%; width: {{rate}}%; height: 14px;"></div>
					</div>
					<p class="description">{{description}}</p>
				</span>
			</div>
			{{/if}}
		{{/each}}		
	</div>