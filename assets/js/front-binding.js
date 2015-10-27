jQuery( function( $ ){

	var forms = $( 'form.caldera_forms_form, .gform_wrapper form, .wpcf7-form, .frm-show-form, .contact-form' ),
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

		if( typeof ga === 'function' ){
			//formworks.config.forms
			var this_form = data.form.split('_');
			if( this_form.length === 1 ){
				this_form[0] = 'caldera';
			}
			// add form name
			if( typeof formworks !== 'undefined' 
				&& typeof formworks.config.forms[ this_form[0] ] !== 'undefined'
				&& typeof formworks.config.forms[ this_form[0] ].forms !== 'undefined'
				){
			
				var form_name = formworks.config.forms[ this_form[0] ].forms[ data.form ];
				if( data.type ){
					ga('send', 'event', { eventCategory: 'Form', eventAction: data.type, eventLabel: form_name });
				}else if( data.field ){
					ga('send', 'event', { eventCategory: 'Form', eventAction: 'Field:' + data.field, eventLabel: form_name });
				}
			}
		}
		if( !data.method ){
			return;
		}
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
			form_id = form.find('[name="_cf_frm_id"]').length ? 'caldera_' + form.find('[name="_cf_frm_id"]').val() : form.prop('id');
		// is cf7
		if( form.find('[name="_wpcf7"]').length ){
			form_id = 'cf7_' + form.find('[name="_wpcf7"]').val();
		}
		// is formiddable
		if( form.find('[name="form_id"]').length ){
			form_id = 'frmid_' + form.find('[name="form_id"]').val();
		}
		if( form.hasClass('contact-form') ){
			form_id = 'jp_' + form.parent().prop('id').replace('contact-form-','');
		}
		form.data('form_id', form_id );
		
		push_stuff( { action : 'frmwks_push', 'type' : 'load', 'form' : form.data('form_id') } );

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
	} );
	if( typeof formworks !== 'undefined' && formworks.submissions !== null ){

		for( var i = 0; i < formworks.submissions.length; i++){
			push_stuff( { action : 'frmwks_push', 'type' : 'submission', 'form' : formworks.submissions[ i ] } );
		}
	}
});