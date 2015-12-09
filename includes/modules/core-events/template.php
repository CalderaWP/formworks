
{{#if datasets}}

	<div id="formworks-main-chart-legend" class="formworks-chart-legend">
		<ul class="line-legend">
		{{#each datasets}}
			<li>
				<label style="margin-right: 12px;">
					<span data-active="{{color}}" style="background-color:{{color}};color: rgb(255, 255, 255); display: inline-block; padding: 8px; border: 2px solid rgb(255, 255, 255);margin: 0px 0px -5px;"></span>
					<input style="display:none;" class="legend-checks legend-{{@key}}" name="legend[{{@key}}]" value="{{@key}}" type="checkbox">
					{{label}}
					
				</label>
			</li>
		{{/each}}
			<li>
				<label style="margin-right: 12px;">
					<span data-active="#333" style="background-color:#333;color: rgb(255, 255, 255); display: inline-block; padding: 8px; border: 2px solid rgb(255, 255, 255);margin: 0px 0px -5px;"></span>
					<input style="display:none;" class="legend-checks legend-post_events" name="legend[post_events]" value="post_events" type="checkbox">
					<?php esc_html_e('Post Events', 'formworks'); ?>
					
				</label>
			</li>
		</ul>
	</div>
	<div>
		{{#if legend/conversion}}<canvas id="formworks-main-chart-conversion" style="width:100%; height: 100px;"></canvas>{{/if}}
		<div id="formworks-main-chart" style="width:100%; height: 250px;"></div>
		<div id="formworks-tooltip" class="postbox" style="display:none;position:absolute;"></div>
	</div>


	{{#script}}
	//<script>
	if( typeof series_legend === 'undefined'){
		var series_legend = {};
		jQuery('.legend-checks').each( function(){
			series_legend[ this.value ] = true;
		});
	}
	jQuery( function( $ ){
		
		$('.legend-checks').on('change', function(){
			var prev = $( this ).prev();
			if( $( this ).is(':checked') ){
				series_legend[ this.value ] = true;	
				prev.css("background-color", prev.data('active'));
			}else{
				series_legend[ this.value ] = false;
				prev.css("background-color", 'transparent');
			}
			
			plot_fromLegend();
		});

		for( var legend in series_legend ){
			$( '.legend-' + legend ).attr('checked', series_legend[ legend ] ).trigger('change');
		}

		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			padding: "2px"
		}).appendTo("body");

		var plot;
		
		function plot_fromLegend(){
			var raw_data = {{{json datasets}}},
				data = [],
				options = {{{json options}}},
				placeholder = $("#formworks-main-chart");
			
				for( var i in raw_data ){
					
					if( series_legend[ i ] ){
						var new_data = [];
						for( var date in raw_data[ i ].data ){
							new_data.push( [ date, raw_data[ i ].data[ date ] ] );
						}
						raw_data[ i ].data = new_data;
						data.push( raw_data[ i ] );
					}

			

				}
			if( ! series_legend.post_events ){
				options.grid.markings = [];
			}
			plot = $.plot( placeholder, data, options );
			if( ! data.length ){
				return;
			}

			if( series_legend.post_events ){
			var o,
				top_start = 8,
				start_left = 0;
			{{#each options/grid/markings}}

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
			}
		}

		plot_fromLegend();

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

	});
	{{/script}}

{{/if}}
