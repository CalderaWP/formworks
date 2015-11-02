jQuery( function( $ ){

	var main_window = $( window ),
		current_request;

	function is_form_visible( form ){

		var docViewTop = main_window.scrollTop();
		var docViewBottom = docViewTop + main_window.height();

		var formTop = form.offset().top;
		var formBottom = formTop + ( form.height() / 2 );

		return ((formBottom <= docViewBottom) && (formTop >= docViewTop));
	}

	function push_stuff( form, data ){

		if( typeof ga === 'function' ){
			// push google stufs
			if( data.type ){
				ga('send', 'event', { eventCategory: 'Form', eventAction: data.type, eventLabel: form.data('form_name') });
			}else if( data.field ){
				ga('send', 'event', { eventCategory: 'Form', eventAction: 'Field:' + data.field, eventLabel: form.data('form_name') });
			}
		}
		if( !data.method ){
			return;
		}
		var ping = new Image();
		ping.src = formworks.frmwksurl + '?' + $.param( data );

	};

	function view_notch( form ){
		if( form.is(':visible') && !form.data('viewNotch') && is_form_visible( form ) ){
			push_stuff( form, { action : 'frmwks_push', 'method' : 'add_notch', 'type' : 'view', 'form' : form.data('form_id') } );
			form.data( 'viewNotch', true );
		}
	}

	function bind_form( form ){
		
		push_stuff( form, { action : 'frmwks_push', 'type' : 'load', 'form' : form.data('form_id') } );

		form.on( 'change' ,'input,select,textarea', function( ){

			var	field = $( this ),
				value = field.val();

			if( field.is(':checkbox') ){
				if( !field.is(':checked') ){
					value = null;
				}
			}
			if( current_request ){
				current_request.abort();
			}
			current_request = push_stuff( form, { action : 'frmwks_push', 'method' : 'add_partial', 'value' : value, 'field' : field.prop('name'), 'form' : form.data('form_id') } );
		});
		form.on( 'focus' ,'input,select,textarea', function( ){
			if( !form.data('engageNotch') ){
				push_stuff( form, { action : 'frmwks_push', 'method' : 'add_notch', 'type' : 'engage', 'form' : form.data('form_id') } );
				form.data('engageNotch', true);
			}else{
				$( this ).trigger( 'change' );
			}
		});

		form.on('submit', function(){
			if( current_request ){
				current_request.abort();
			}
		});

		main_window.on( 'scroll', function(){
			view_notch( form );
		});
		view_notch( form );
	};

	var current_form;
	if( formworks && formworks.config && formworks.config.selectors ){
		for( var i = 0; i < formworks.config.selectors.length; i++ ){

			current_form = $( formworks.config.selectors[ i ].selector );
			if( current_form.length ){
				current_form.data('form_id', formworks.config.selectors[ i ].prefix + '_' + formworks.config.selectors[ i ].id );
				current_form.data('form_name', formworks.config.selectors[ i ].name );
				bind_form( current_form );
			}
		}
	}

	if( typeof formworks !== 'undefined' && formworks.submissions && formworks.submissions !== null ){

		for( var i = 0; i < formworks.submissions.length; i++){
			push_stuff( form, { action : 'frmwks_push', 'type' : 'submission', 'form' : formworks.submissions[ i ] } );
		}
	}
});