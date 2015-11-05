{{#if data/datasets}}

	<div id="formworks-main-chart-legend" class="formworks-chart-legend">
		<ul class="line-legend">
		{{#each data/datasetss}}
			{{#unless hide}}
			<li>
				<label>
					<span style="background-color:{{color}};color: rgb(255, 255, 255); border-radius: 3px; padding: 2px 6px 1px 3px;">
						<input style="display:none;" type="checkbox" value="1" checked="checked">
						{{label}}
					</span>
					
				</label>
			</li>
			{{/unless}}
		{{/each}}
		</ul>
	</div>
	<div>
		{{#unless data}}<span style="visibility: visible; float: none;" class="spinner"></span>{{/unless}}
		{{#if data/legend/conversion}}<canvas id="formworks-main-chart-conversion" style="width:100%; height: 100px;"></canvas>{{/if}}
		<div id="formworks-main-chart" style="width:100%; height: 250px;"></div>
		<div id="formworks-tooltip" class="postbox" style="display:none;position:absolute;"></div>
	</div>


	{{#script}}
	//<script>
	
	jQuery( function( $ ){

		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			padding: "2px"
		}).appendTo("body");

		var raw_data = {{{json @root/data/datasets}}},
			data = [],
			options = {{{json @root/data/options}}},
			placeholder = $("#formworks-main-chart");
		
			for( var i in raw_data ){
				
				var new_data = [];
				for( var date in raw_data[ i ].data ){
					new_data.push( [ date, raw_data[ i ].data[ date ] ] );
				}
				raw_data[ i ].data = new_data;
				data.push( raw_data[ i ] );

		

			}

		


		var plot = $.plot( placeholder, data, options );

		var o,
			top_start = 8,
			start_left = 0;
		{{#each @root/data/options/grid/markings}}

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