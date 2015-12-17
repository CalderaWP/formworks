var formworks_canvas = false,
	frmwks_get_config_object,
	frmwks_record_change,
	frmwks_canvas_reset,
	frmwks_canvas_init,
	frmwks_rebuild_canvas,
	frmwks_add_node,
	frmwks_get_default_setting,
	frmwks_code_editor,
	frmwks_handle_save,	
	frmwks_rebuild_magics,
	frmwks_is_view,
	frmwks_init_magic_tags,
	frmwks_get_filters,
	frmwks_config_object = {},
	frmwks_magic_tags = [],
	frmwks_prepdb_rebuild;

jQuery( function($){
	frmwks_prepdb_rebuild = function(){
		return {};
	}

	frmwks_get_filters = function( el ){
		var backdrop = $('<div class="baldrick-backdrop" style="display: none;"><div class="formworks-loader"></div></div>');
		$('#formworks-main-canvas').append( backdrop );
		backdrop.fadeIn(200);
		var module = $( el );
		frmwks_get_config_object();
		if( frmwks_config_object.filters ){
			$( el ).data( { 
				id : frmwks_config_object.form_id,
				prefix : frmwks_config_object.form_slug,
				filters : JSON.stringify( frmwks_config_object.filters ),
				modules : JSON.stringify( frmwks_config_object.module ),
			} );
		}
	}

	frmwks_handle_save = function( obj ){

		var notice;

		if( obj.data.success ){
			notice = $('.updated_notice_box');
		}else{
			notice = $('.error_notice_box');
		}

		notice.stop().animate({top: 32}, 200, function(){
			setTimeout( function(){
				notice.stop().animate({top: -175}, 200);
			}, 2000);
		});

	}


	frmwks_init_magic_tags = function(){
		//init magic tags
		var magicfields = jQuery('.magic-tag-enabled');

		magicfields.each(function(k,v){
			var input = jQuery(v);
			
			if(input.hasClass('magic-tag-init-bound')){
				var currentwrapper = input.parent().find('.magic-tag-init');
				if(!input.is(':visible')){
					currentwrapper.hide();
				}else{
					currentwrapper.show();
				}
				return;			
			}
			var magictag = jQuery('<span class="dashicons dashicons-editor-code magic-tag-init"></span>'),
				wrapper = jQuery('<span style="position:relative;display:inline-block; width:100%;"></span>');

			if(input.is('input')){
				magictag.css('borderBottom', 'none');
			}

			if(input.hasClass('formworks-conditional-value-field')){
				wrapper.width('auto');
			}

			//input.wrap(wrapper);
			magictag.insertAfter(input);
			input.addClass('magic-tag-init-bound');
			if(!input.is(':visible')){
				magictag.hide();
			}else{
				magictag.show();
			}
		});

	}

	// internal function declarationas
	frmwks_get_config_object = function(el){
		// new sync first
		$('#formworks-id').trigger('change');
		var clicked 	= $(el),
			config 		= $('#formworks-live-config').val(),
			required 	= $('[required]'),
			clean		= true;

		for( var input = 0; input < required.length; input++ ){
			if( required[input].value.length <= 0 && $( required[input] ).is(':visible') ){
				$( required[input] ).addClass('formworks-input-error');
				clean = false;
			}else{
				$( required[input] ).removeClass('formworks-input-error');
			}
		}
		if( clean ){
			formworks_canvas = config;
		}
		clicked.data( 'config', config );
		return clean;
	}

	frmwks_record_change = function(){
		// hook and rebuild the fields list
		jQuery(document).trigger('record_change');
		jQuery('#formworks-id').trigger('change');
		if( frmwks_config_object ){
			jQuery('#formworks-field-sync').trigger('refresh');
		}
	}

	frmwks_canvas_reset = function(el, ev){
		// handy to add things before builing/rebuilding the canvas
		// return false to stop.

		// remove editors and quicktags
		if ( typeof tinymce !== 'undefined' ) {
			for ( ed in tinymce.editors ) {
				tinymce.editors[ed].remove();
			}
		}
		if ( typeof QTags !== 'undefined' ) {
			QTags.buttonsInitDone = false;
			QTags.instances = {};
		}

		return true
	}
	
	frmwks_canvas_init = function(){

		if( !formworks_canvas ){
			// bind changes
			jQuery('#formworks-main-canvas').on('keydown keyup change','input, select, textarea', function(e) {
				frmwks_config_object = jQuery('#formworks-main-form').formJSON(); // perhaps load into memory to keep it live.
				jQuery('#formworks-live-config').val( JSON.stringify( frmwks_config_object ) ).trigger('change');
			});
			// bind editor
			frmwks_init_editor();
			formworks_canvas = jQuery('#formworks-live-config').val();
			frmwks_config_object = JSON.parse( formworks_canvas ); // perhaps load into memory to keep it live.
			// wp_editor
			if ( typeof tinymce !== 'undefined' ) {
				tinymce.on('AddEditor', function(e) {

					e.editor.on('keyup', function (e) { 
						this.save();
						jQuery( this.targetElm ).trigger('keyup');
					});
					e.editor.on('change', function (e) { 
						this.save();
						jQuery( this.targetElm ).trigger('change');
					});
				});
			}
		}
		if( $('.color-field').length ){
			$('.color-field').wpColorPicker({
				change: function(obj){
					
					var trigger = $(this);
					if( trigger.data('target') ){
						$( trigger.data('target') ).css( trigger.data('style'), trigger.val() );
					}
					
				}
			});
		}
		if( $('.formworks-group-wrapper').length ){
			$( ".formworks-group-wrapper" ).sortable({
				handle: ".sortable-item",
				start: function(ev, ui){
					ui.item.data('moved', true);
					ui.placeholder.css( 'borderWidth', ui.item.css( 'borderTopWidth') );
					ui.placeholder.css( 'margin', ui.item.css( 'marginTop') );					
				},
				update: function(ev, ui){
					jQuery('#formworks-id').trigger('change');
				}
			});
			$( ".formworks-fields-list" ).sortable({
				handle: ".sortable-item",
				update: function(){
					jQuery('#formworks-id').trigger('change');
				}
			});
		}

		//wp_editor refresh
		frmwks_init_wp_editors();

		// live change init
		$('[data-init-change]').trigger('change');
		$('[data-auto-focus]').focus().select();

		// rebuild tags
		frmwks_rebuild_magics();
		jQuery(document).trigger('canvas_init');
	}
	
	frmwks_add_node = function(node, node_default){
		var id = 'nd' + Math.round(Math.random() * 99866) + Math.round(Math.random() * 99866),
			newnode = { "_id" : id },
			nodes = node.split('.'),
			node_point_record = nodes.join('.') + '.' + id,
			node_defaults = JSON.parse( '{ "_id" : "' + id + '", "_node_point" : "' + node_point_record + '" }' );

		if( node_default && typeof node_default === 'object' ){				
			$.extend( true, node_defaults, node_default );
		}			
		var node_string = '{ "' + nodes.join( '": { "') + '" : { "' + id + '" : ' + JSON.stringify( node_defaults );
		for( var cls = 0; cls <= nodes.length; cls++){
			node_string += '}';
		}
		var new_nodes = JSON.parse( node_string );
		$.extend( true, frmwks_config_object, new_nodes );
	};

	frmwks_get_default_setting = function(obj){

		var id = 'nd' + Math.round(Math.random() * 99866) + Math.round(Math.random() * 99866),
			trigger = ( obj.trigger ? obj.trigger : obj.params.trigger ),
			sub_id = ( trigger.data('group') ? trigger.data('group') : 'nd' + Math.round(Math.random() * 99766) + Math.round(Math.random() * 99866) ),
			nodes;

		
		// add simple node
		if( trigger.data('addNode') ){
			// new node? add one
			frmwks_add_node( trigger.data('addNode'), trigger.data('nodeDefault') );
		}
		// remove simple node (all)
		if( trigger.data('removeNode') ){
			// new node? add one
			if( frmwks_config_object[trigger.data('removeNode')] ){
				delete frmwks_config_object[trigger.data('removeNode')];
			}

		}

		switch( trigger.data('script') ){
			case "add-to-object":
				// add to core object
				//frmwks_config_object.entry_name = obj.data.value; // ajax method

				break;
			case "add-field-node":
				// add to core object
				if( !frmwks_config_object[trigger.data('slug')][trigger.data('group')].field ){
					frmwks_config_object[trigger.data('slug')][trigger.data('group')].field = {};
				}
				frmwks_config_object[trigger.data('slug')][trigger.data('group')].field[id] = { "_id": id, 'name': 'new field', 'slug': 'new_field' };
				frmwks_config_object.open_field = id;
				break;				
		}

		frmwks_rebuild_canvas();

	};

	frmwks_rebuild_canvas = function(){
		jQuery('#formworks-live-config').val( JSON.stringify( frmwks_config_object ) );
		jQuery('#formworks-field-sync').trigger('refresh');	
	};
	// sutocomplete category
	$.widget( "custom.catcomplete", $.ui.autocomplete, {
		_create: function() {
			this._super();
			this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
		},
		_renderMenu: function( ul, items ) {
			var that = this,
			currentCategory = "";
			$.each( items, function( index, item ) {
				var li;
				if ( item.category != currentCategory ) {
					ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
					currentCategory = item.category;
				}
				li = that._renderItemData( ul, item );
				if ( item.category ) {
					li.attr( "aria-label", item.category + " : " + item.label );
				}
			});
		}
	});
	frmwks_rebuild_magics = function(){

		function split( val ) {
			return val.split( / \s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}
		$( ".magic-tag-enabled" ).bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB && $( this ).catcomplete( "instance" ).menu.active ) {
				event.preventDefault();
			}
		}).catcomplete({
			minLength: 0,
			source: function( request, response ) {
				// delegate back to autocomplete, but extract the last term
				frmwks_magic_tags = [];
				var category = '',
					tags = $('.formworks-magic-tags-definitions');

				if( tags.length ){

					for( var tag_set = 0; tag_set < tags.length; tag_set++ ){

						var magic_tags;
						
						category = 'Magic Tags';

						if( $( tags[ tag_set ] ).data('category') ){
							category = $( tags[ tag_set ] ).data('category');
						}
						// set internal tags
						try{
							magic_tags = JSON.parse( tags[ tag_set ].value );
						} catch (e) {
							magic_tags = [ $(tags[ tag_set ]).data('tag') ];
						}

						var display_label;
						for( f = 0; f < magic_tags.length; f++ ){
							display_label = magic_tags[f].split( '*' );
							if( display_label[1] ){
								display_label = display_label[0] + '*';
							}
							frmwks_magic_tags.push( { label: '{' + display_label + '}',value: '{' + magic_tags[f] + '}', category: category }  );
						}
					}

				}
				
				response( $.ui.autocomplete.filter( frmwks_magic_tags, extractLast( request.term ) ) );
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				//terms.push( "" );
				this.value = terms.join( " " );
				return false;
			}
		});
	}

	frmwks_init_wp_editors = function(){

		if( typeof tinyMCEPreInit === 'undefined'){
			return;
		}

		var ed, init, edId, qtId, firstInit, wrapper;

		if ( typeof tinymce !== 'undefined' ) {

			for ( edId in tinyMCEPreInit.mceInit ) {

				if ( firstInit ) {
					init = tinyMCEPreInit.mceInit[edId] = tinymce.extend( {}, firstInit, tinyMCEPreInit.mceInit[edId] );
				} else {
					init = firstInit = tinyMCEPreInit.mceInit[edId];
				}

				wrapper = tinymce.DOM.select( '#wp-' + edId + '-wrap' )[0];

				if ( ( tinymce.DOM.hasClass( wrapper, 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( edId ) ) &&
					! init.wp_skip_init ) {

					try {
						tinymce.init( init );

						if ( ! window.wpActiveEditor ) {
							window.wpActiveEditor = edId;
						}
					} catch(e){}
				}
			}
		}
		
		for ( qtId in tinyMCEPreInit.qtInit ) {
			try {
				quicktags( tinyMCEPreInit.qtInit[qtId] );

				if ( ! window.wpActiveEditor ) {
					window.wpActiveEditor = qtId;
				}
			} catch(e){};
		}

		jQuery('.wp-editor-wrap').on( 'click.wp-editor', function() {

			if ( this.id ) {
				window.wpActiveEditor = this.id.slice( 3, -5 );
			}
		});


	}

	// trash 
	$(document).on('click', '.formworks-card-actions .confirm a', function(e){
		e.preventDefault();
		var parent = $(this).closest('.formworks-card-content');
			actions = parent.find('.row-actions');

		actions.slideToggle(300);
	});

	// bind slugs
	$(document).on('keyup change', '[data-format="slug"]', function(e){

		var input = $(this);

		if( input.data('master') && input.prop('required') && this.value.length <= 0 && e.type === "change" ){
			this.value = $(input.data('master')).val().replace(/[^a-z0-9]/gi, '_').toLowerCase();
			if( this.value.length ){
				input.trigger('change');
			}
			return;
		}

		this.value = this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();
	});
	// init partials
	$('script[data-handlebars-partial]').each( function(){
		var partial = $( this );
		Handlebars.registerPartial( partial.data('handlebarsPartial'), partial.html() );
	});	
	// bind label update
	$(document).on('keyup change', '[data-sync]', function(){
		var input = $(this),
			syncs = $(input.data('sync'));
		
		syncs.each(function(){
			var sync = $(this);

			if( sync.is('input') ){
				sync.val( input.val() ).trigger('change');
			}else{
				sync.text(input.val());
			}
		});
	});

	$('body').on('click', 'label.formworks-filter-button', function(){

		$('.formworks-filter-button.wp-baldrick.active').trigger('click');

	});	

	// bind tabs
	$(document).on('click', '.formworks-nav-tabs a', function(e){
		
		e.preventDefault();
		var clicked 	= $(this),
			tab_id 		= clicked.attr('href'),
			required 	= $('[required]'),
			clean		= true;

		for( var input = 0; input < required.length; input++ ){
			if( required[input].value.length <= 0 && $( required[input] ).is(':visible') ){
				$( required[input] ).addClass('formworks-input-error');
				clean = false;
			}else{
				$( required[input] ).removeClass('formworks-input-error');
			}
		}
		if( !clean ){
			return;
		}

		if( frmwks_code_editor ){
			frmwks_code_editor.toTextArea();
			frmwks_code_editor = false;
		}

		if( $( tab_id ).find('.formworks-code-editor').length ){

			frmwks_init_editor( $( tab_id ).find('.formworks-code-editor').prop('id') );
			frmwks_code_editor.refresh();
			frmwks_code_editor.focus();
		}

		jQuery('#formworks-active-tab').val(tab_id).trigger('change');
		frmwks_record_change();
	});

	// row remover global neeto
	$(document).on('click', '[data-remove-parent]', function(e){
		var clicked = $(this),
			parent = clicked.closest(clicked.data('removeParent'));
		if( clicked.data('confirm') ){
			if( !confirm(clicked.data('confirm')) ){
				return;
			}
		}
		parent.remove();
		frmwks_record_change();
	});
	
	// row remover global neeto
	$(document).on('click', '[data-remove-element]', function(e){
		var clicked = $(this),
			elements = $(clicked.data('removeElement'));
		if( clicked.data('confirm') ){
			if( !confirm(clicked.data('confirm')) ){
				return;
			}
		}
		elements.remove();
		frmwks_record_change();
	});

	// init tags
	$('body').on('click', '.magic-tag-init', function(e){
		var clicked = $(this),
			input = clicked.prev();

		input.focus().trigger('init.magic');

	});
	
	// initialize live sync rebuild
	$(document).on('change', '[data-live-sync]', function(e){
		frmwks_record_change();
	});
	// initialize live sync rebuild
	$(document).on('click', '.apply-filters', function(e){
		$('.stat-module').trigger('reload');
	});	
	// initialize live sync rebuild
	$(document).on('change', '.preset-radio', function(e){
		var radios = $('.preset-radio');
		radios.each( function(){
			var parent = $(this).parent();
			parent.removeClass('active');
			if( $(this).is(':checked') ){
				parent.addClass('active');
			}
		});

		if( $(this).parent().hasClass('date-range') ){
			$('.input-daterange').fadeIn( 200 );
		}else{
			$('.input-daterange').fadeOut( 200 );
		}
		$('.apply-filters').fadeIn();
	});	
	$(document).on('change', '.preset-check', function(e){
		var parent = $(this).parent();
		if( $(this).is(':checked') ){
			parent.addClass('active');
		}else{
			parent.removeClass('active');
		}
		$('.apply-filters').fadeIn();

	});	

	// initialise baldrick triggers
	$('.wp-baldrick').baldrick({
		request     : ajaxurl,
		method      : 'POST',
		before		: function(el){
			
			var tr = $(el);

			if( tr.data('addNode') && !tr.data('request') ){
				tr.data('request', 'frmwks_get_default_setting');
			}
		},
		complete : function(){
			$('.apply-filters').fadeOut();
		}
	});


	window.onbeforeunload = function(e) {

		if( formworks_canvas && formworks_canvas !== jQuery('#formworks-live-config').val() && !frmwks_is_view && !jQuery('.formworks-admin').length ){
			return true;
		}
	};


});







function frmwks_init_editor(el){
	if( !jQuery('#' + el).length ){
		return;
	}	
	// custom modes
	var mustache = function(formworks_init, state) {

		var ch;

		if (formworks_init.match("{{")) {
			while ((ch = formworks_init.next()) != null){
				if (ch == "}" && formworks_init.next() == "}") break;
			}
			formworks_init.eat("}");
			return "mustache";
		}
		/*
		if (formworks_init.match("{")) {
			while ((ch = formworks_init.next()) != null)
				if (ch == "}") break;
			formworks_init.eat("}");
			return "mustacheinternal";
		}*/
		if (formworks_init.match("%")) {
			while ((ch = formworks_init.next()) != null)
				if (ch == "%") break;
			formworks_init.eat("%");
			return "command";
		}

		/*
		if (formworks_init.match("[[")) {
			while ((ch = formworks_init.next()) != null)
				if (ch == "]" && formworks_init.next() == "]") break;
			formworks_init.eat("]");
			return "include";
		}*/
		while (formworks_init.next() != null && 
			//!formworks_init.match("{", false) && 
			!formworks_init.match("{{", false) && 
			!formworks_init.match("%", false) ) {}
			return null;
	};

	var options = {
		lineNumbers: true,
		matchBrackets: true,
		tabSize: 2,
		indentUnit: 2,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		lineWrapping: true,
		extraKeys: {"Ctrl-Space": "autocomplete"},
		};
	// base mode

	CodeMirror.defineMode("mustache", function(config, parserConfig) {
		var mustacheOverlay = {
			token: mustache
		};
		return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || 'text/html' ), mustacheOverlay);
	});
	options.mode = jQuery('#' + el).data('mode') ? jQuery('#' + el).data('mode') : "mustache";

	frmwks_code_editor = CodeMirror.fromTextArea(document.getElementById(el), options);
	frmwks_code_editor.on('keyup', tagFields);
	frmwks_code_editor.on('blur', function(cm){
		cm.save();
		jQuery( cm.getInputField() ).trigger('change');
	});

	return frmwks_code_editor;

}
