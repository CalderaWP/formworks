
jQuery(function($){


	$('body').on('click', '#formworks-insert', function(e){

		e.preventDefault();
		var modal = $('.formworks-insert-modal'),
			clicked = $(this);

		if( clicked.data('selected') ){
			clicked.data('selected', null);
		}


		modal.fadeIn(100);

	});

	$('body').on('click', '.formworks-modal-closer', function(e){
		e.preventDefault();
		var modal = $('.formworks-insert-modal');
		modal.fadeOut(100);		
	});

	$('body').on('click', '.formworks-shortcode-insert', function(e){
	 	
	 	e.preventDefault();
	 	var formworks = $('.selected-formworks-shortcode:checked'),code;

	 	if(!formworks.length){
	 		return;
	 	}

	 	code = '[formworks slug="' + formworks.val() + '"]';

	 	formworks.prop('checked', false);	 	
		window.send_to_editor(code);
		$('.formworks-modal-closer').trigger('click');

	});

});//
