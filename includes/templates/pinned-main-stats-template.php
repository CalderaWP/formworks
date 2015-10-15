<?php
/**
 * partioal template for Main Stats
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */

?>
<h4 style="float: left; margin: 0 18px 0px 0px;">
	<?php _e('Form Statistics', 'formworks') ; ?>
	<small class="description">
		<?php _e('Analytics', 'formworks') ; ?>
	</small>
</h4>
{{#if main_stats}}
<div style="position: relative; top: -5px;">
	<button type="button" class="add-new-h2 formworks-filter-button wp-baldrick {{#is main_stats/filter value="this_week"}} active{{/is}}{{#unless main_stats/filter}} active{{/unless}}" data-preset="this_week" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>"><?php _e('This Week', 'formworks' ); ?></button>
	<button type="button" class="add-new-h2 formworks-filter-button wp-baldrick {{#is main_stats/filter value="this_month"}} active{{/is}}" data-preset="this_month" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>"><?php _e('This Month', 'formworks' ); ?></button>
	<button type="button" class="add-new-h2 formworks-filter-button wp-baldrick {{#is main_stats/filter value="last_month"}} active{{/is}}" data-preset="last_month" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>"><?php _e('Last Month', 'formworks' ); ?></button>

	<button style="margin: 0px 0px 0px 20px;" type="button" class="add-new-h2 formworks-filter-button wp-baldrick {{#is main_stats/filter value="custom"}} active{{/is}}" data-preset="custom" data-end="{{main_stats/end}}" data-start="{{main_stats/start}}" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>"><?php _e('Custom Range', 'formworks' ); ?></button>

	<div style="display: inline-block;" class="formwork-datepicker input-daterange input-group" id="formworks-range-datepicker">		
	    <input style="width: 120px;" type="text" class="formworks-date-input wp-baldrick" data-event="change" data-preset="custom" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>" data-end="{{main_stats/end}}" name="start" value="{{main_stats/start}}" />
	    <span style="display: inline-block; margin: -4px; padding: 5px 6px; background: rgb(224, 224, 224) none repeat scroll 0% 0%;"><?php _e( 'to', 'formworks' ); ?></span>
	    <input style="width: 120px;" type="text" class="formworks-date-input wp-baldrick" data-event="change" data-preset="custom" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>" data-start="{{main_stats/start}}" name="end" value="{{main_stats/end}}" />
	</div>
</div>
{{#script}}
jQuery( function( $ ) {
	$('.input-daterange').datepicker({
		format: "yyyy-mm-dd",
		orientation: "bottom left",
		autoclose: true
	}).on('changeDate', function(){
		console.log( arguments );
	});
});

{{/script}}

{{/if}}

<input name="main_stats" id="formworks-main-stats" value="{{#if main_stats}}{{json main_stats}}{{/if}}" type="hidden" data-live-sync="true">
{{#unless main_stats}}
	<span class="wp-baldrick" data-action="frmwks_get_mainstats" data-target="#formworks-main-stats" data-form="<?php echo substr( $formworks['id'], 2 ); ?>" data-autoload="true"></span>
	<input type="hidden" name="legend" value="{{json legend}}">
{{/unless}}

	
	<div id="formworks-main-chart-legend" class="formworks-chart-legend">
		<ul class="line-legend">
		{{#each main_stats/datasets}}
			{{#unless hide}}
			<li><label><input style="display:none;" data-colorhack="{{strokeColor}}" data-name="{{label}}" data-live-sync="true" type="checkbox" value="1" name="legend[{{@key}}]" {{#find @root/legend @key}}checked="checked"{{/find}}>
				<span style="background-color:{{#find @root/legend @key}}{{#if ../color}}{{../color}}{{else}}#333{{/if}}{{else}}#efefef{{/find}};"></span>
				{{label}}</label>
			</li>
			{{/unless}}
		{{/each}}
		{{#if @root/main_stats/options/grid/markings}}
			<li><label><input style="display:none;" data-colorhack="{{strokeColor}}" data-name="{{label}}" data-live-sync="true" type="checkbox" value="1" name="show_events" {{#if @root/show_events}}checked="checked"{{/if}}>
				<span style="background-color:{{#unless @root/show_events}}#333{{else}}#efefef{{/unless}};"></span>
				<?php _e('Show Post Events', 'formworks'); ?></label>
			</li>
		{{/if}}
		</ul>
	</div>
	<div>
		{{#unless main_stats}}<span style="visibility: visible; float: none;" class="spinner"></span>{{/unless}}
		{{#if legend/conversion}}<canvas id="formworks-main-chart-conversion" style="width:100%; height: 100px;"></canvas>{{/if}}
		<div id="formworks-main-chart" style="width:100%; height: 250px;"></div>
		<div id="formworks-tooltip" class="postbox" style="display:none;position:absolute;"></div>
	</div>

	<div style="width: 33%; float: left; padding: 0px 12px 0px 0px; box-sizing: padding-box;">
	{{#if main_stats/conversion_story}}
		<h4><?php _e('Report Summary', 'formworks'); ?></h4>
		<p>{{{main_stats/conversion_story}}}</p>
	{{/if}}
	</div>

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

	<div class="clear"></div>

{{#if main_stats}}	
	{{#script}}
	//<script>
	// Get the context of the canvas element we want to select
	//var ctx = document.getElementById("formworks-main-chart").getContext("2d");
	{{#if legend/conversion}}
	//var ctx_conv = document.getElementById("formworks-main-chart-conversion").getContext("2d");
	{{/if}}
	var config = {
			scaleFontSize		: 10,
			maintainAspectRatio : true,
			animation			:	false,
			responsive			: true,
			datasetFill 		: false,
			bezierCurveTension 	: 0.1,
			datasetStrokeWidth 	: 1,
			tooltipCornerRadius	: 2,
			tooltipFontColor 	: '#333',
			tooltipTitleFontColor : '#333',
			tooltipFontSize		: 12,
			tooltipFillColor	: '#fff',			
			customTooltips: function(tooltip) {
		        var tooltipEl = jQuery('#formworks-tooltip');
		        if (!tooltip) {
		            tooltipEl.css({
		                opacity: 0,
		                display: "none"
		            });
		            return;
		        }
		        tooltipEl.removeClass('above below');
		        tooltipEl.addClass(tooltip.yAlign);
		        var innerHtml = '<div style="border-bottom: 1px solid rgb(223, 223, 223); margin-bottom: -1px; padding: 3px 13px; background: rgb(245, 245, 245) none repeat scroll 0% 0%; font-weight: bold; color: rgb(111, 111, 111);">' + tooltip.title + '</div>';

		        for (var i = tooltip.labels.length - 1; i >= 0; i--) {

		        	innerHtml += [
		        		'<div  style="white-space:nowrap;">',
		        		//'	<span style="padding:5px 5px 5px 11px;color:#333;display: inline-block; width: 90px;box-shadow: 6px 0 0 ' + tooltip.legendColors[i].fill + ' inset;">' + jQuery('[data-colorhack="' + tooltip.legendColors[i].fill + '"]').data('name') + '</span>',
		        		'	<span style="padding:5px 5px 5px 11px;color:#333;display: inline-block; min-width: 30px;box-shadow: 6px 0 0 ' + tooltip.legendColors[i].fill + ' inset;">' + tooltip.labels[i] + '</span>',
		        		//'	<span style="padding:5px;">' + tooltip.labels[i] + '</span>',
		        		'</div>'
		        	].join('');
		        }
		        tooltipEl.html(innerHtml);
		        tooltipEl.css({
		            opacity: 1,
		            display: 'block',
		            left: tooltip.x + 'px',
		            top: tooltip.chart.canvas.offsetTop + tooltip.y - tooltipEl.height() + 'px',
		            fontFamily: tooltip.fontFamily,
		            fontSize: tooltip.fontSize,
		            fontStyle: tooltip.fontStyle,
		            minWidth: 80,
		        });
		    }
		  };
	var data = { labels : {{{json main_stats/labels}}}, datasets : [ { data : [] } ] };
	var conv_data = { labels : {{{json main_stats/labels}}}, datasets : [ { data : [] } ] };

	var tooltip_labels = [];
	{{#each legend}}

		{{#find @root/main_stats/datasets @key}}
			{{#is @key value="conversion"}}
			conv_data.datasets.push( {{{json this}}} );
			{{else}}
			tooltip_labels.push( '{{label}}' );
			data.datasets.push( {{{json this}}} );
			{{/is}}
		{{/find}}
	{{/each}}

	/*
	var formworks_main_chart = new Chart(ctx).Line( data, config );
	{{#if legend/conversion}}
	var conf_config = config;
	conf_config.showScale = true;
	conf_config.scaleShowLabels = true;
	conf_config.multiTooltipTemplate = "<%= value %> %";
	var formworks_main_chart_conv = new Chart(ctx_conv).Line( conv_data, conf_config );
	{{/if}}
	*/

	jQuery( function( $ ){

		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			padding: "2px"
		}).appendTo("body");

		var data = [],
			raw_data = {{{json @root/main_stats/datasets}}},
			options = {{{json @root/main_stats/options}}},
			placeholder = $("#formworks-main-chart"),
			legend = [];
			{{#each @root/legend}}
				legend.push( '{{@key}}' );
			{{/each}}

			for( var i in raw_data ){
				if( !!~legend.indexOf( i ) ){
					var new_data = [];
					for( var date in raw_data[ i ].data ){
						new_data.push( [ date, raw_data[ i ].data[ date ] ] );
					}
					raw_data[ i ].data = new_data;
					data.push( raw_data[ i ] );
				}
			}

		{{#if @root/main_stats/options/grid/markings}}
			{{#if @root/show_events}}
				options.grid.markings = [];
			{{/if}}
		{{/if}}
		
		var plot = $.plot( placeholder, data, options );


		{{#if @root/main_stats/options/grid/markings}}
			{{#unless @root/show_events}}
			var o,
				top_start = 8,
				start_left = 0;
			{{#each @root/main_stats/options/grid/markings}}

				o = plot.pointOffset({ x: {{xaxis/from}}, y: 1});				
				placeholder.prepend("<div class='formworks-post-event' style='position:absolute;left:" + (o.left) + "px;top:" + top_start + "px;color:#666;font-size:smaller;background: rgb(255, 255, 255) none repeat scroll 0% 0%; padding: 0px 8px; border: 1px solid rgb(207, 207, 207);' title='{{label}}'>{{label}}</div>");
				if( start_left !== {{xaxis/from}} ){
					//top_start == 0;
					start_left = {{xaxis/from}};
				}
				top_start += 22;
				if( top_start > 180 ){
					top_start = 8;
				}
			{{/each}}
			{{/unless}}
		{{/if}}

		$("#formworks-main-chart").bind("plothover", function (event, pos, item) {

			var str = "(" + pos.x + ", " + pos.y + ")";
			$("#hoverdata").text(str);

			if (item) {
				var x = item.datapoint[0],
					y = item.datapoint[1];

				$("#tooltip").html(item.series.label + ": " + y)
					.css({top: item.pageY+15, left: item.pageX+5})
					.fadeIn(200).css({"background-color" : item.series.color, color : '#fff' } )
			} else {
				$("#tooltip").hide();
			}

		});


		//$('#formworks-main-chart-legend').html( formworks_main_chart.generateLegend() );
		$( window ).on( 'resize', function(){
			//formworks_main_chart.resize();
		} );
	});
	{{/script}}
{{/if}}