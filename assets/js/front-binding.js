
jQuery( function( $ ){

	var forms = $( 'form.caldera_forms_form, .gform_wrapper form, .wpcf7-form' ),
		main_window = $( window ),
		current_request;

	function is_form_visible( form ){

		var docViewTop = main_window.scrollTop();
		var docViewBottom = docViewTop + main_window.height();

		var formTop = form.offset().top;
		var formBottom = formTop + ( form.height() / 2 );

		return ((formBottom <= docViewBottom) && (formTop >= docViewTop));
	}

	function push_stuff( data ){
		
		return $.post( formworks.frmwksurl, data );

	};

	function view_notch( form ){
		if( form.is(':visible') && !form.data('viewNotch') && is_form_visible( form ) ){
			push_stuff( { action : 'frmwks_push', 'method' : 'add_notch', 'type' : 'view', 'form' : form.data('form_id') } );
			form.data( 'viewNotch', true );
		}
	}

	forms.each( function(){

		var form = $( this ),
			form_id = form.find('[name="_cf_frm_id"]').length ? form.find('[name="_cf_frm_id"]').val() : form.prop('id');
		// is cf7
		if( form.find('[name="_wpcf7"]').length ){
			form_id = 'cf7_' + form.find('[name="_wpcf7"]').val();
		}
		form.data('form_id', form_id );
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
			current_request = push_stuff( { action : 'frmwks_push', 'method' : 'add_partial', 'value' : value, 'field' : field.prop('name'), 'form' : form.data('form_id') } );
		});
		form.on( 'focus' ,'input,select,textarea', function( ){
			if( !form.data('engageNotch') ){
				push_stuff( { action : 'frmwks_push', 'method' : 'add_notch', 'type' : 'engage', 'form' : form.data('form_id') } );
				form.data('engageNotch', true);
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
	} );

});