(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 $(function(){

	 	 $(document).on('click','.add_new_row',function(e){
		 	e.preventDefault();
		 	
		 	
		 	var $parent = $('.repeater-metabox');
		 	var selector = $('.repeater-metabox .repeater .form-group:first-child');

		 	var checkClass =selector.hasClass('hidden');
		 	if(checkClass) 
		 		selector.removeClass('hidden').css({'display':'block'});
		 	else {
		    	var   $repeater = $parent.find('.repeater:first-child').find('[data-repeatable]');
		   		var   count = $repeater.length;
		    	var   $clone = $repeater.first().clone(true);	      
		        $clone.find('[id]').each(function() {
				    this.id = this.id + '_' + count;
				});
				$clone.find('input').each(function() {
				    this.value = '';
				});		
				$clone.find('.required').removeClass('errorClass');
				$clone.find('i').removeClass('green dashicons-plus-alt').addClass('red dashicons-minus');
				$clone.find('a').removeClass('add_new_row').addClass('remove_row');
				$clone.find('.extrafield').attr('type','hidden');
				$clone.find('option:selected').removeAttr('selected');
				$parent.find('.repeater').append($clone);
			}
		})
	 	$(document).on('click','.remove_row',function(e){
	 		e.preventDefault();
	 		var box_length = $('.repeater-metabox .repeater .form-group').length;
	 		var $this = $(this);
 			if(box_length>1) $this.closest('.form-group').remove();
 			else {
 				$this.closest('.form-group').find('option:selected').removeAttr('selected');
 				$this.closest('.form-group').find('input').val('');
 				$this.closest('.form-group').addClass('hidden').css({'display':'none'});
 			}
	 	})
	 	$(document).on('click','.btnWorkoutSubmit',function(){
	 		var form  = $(this).closest('form');
	 		var isErrorFound = false;
	 		form.find('input,select').each(function(){
	 			var selector = $(this);
	 			if(selector.hasClass('required')) {
	 				if(selector.val()=='') {
	 					selector.addClass('errorClass');
	 					isErrorFound=true;
	 				}
	 				else {
	 					selector.removeClass('errorClass');
	 				}
	 			}

	 		//	console.log($(this));
	 		})
	 		if(isErrorFound) return false;
	 		$.ajax({
	 			url:wmp_vars.ajax_url,
	 			'type':'POST',
	 			data:form.serialize(),
	 			beforeSend:function() {
	 				form.find('.spinner').addClass('is-active');
	 				$('.btnWorkoutSubmit').attr('disabled','disabled');
	 			},
	 			success:function(res) {
	 				var className = (res.status) ? 'success' : 'error';
	 				$('#wmp-message').addClass('notice notice-'+className).html(`<p> ${res.msg}`);
	 				form[0].reset();
	 				form.find('.spinner').removeClass('is-active');
	 				$('.btnWorkoutSubmit').removeAttr('disabled');
	 				setTimeout(function(){
	 					window.location.reload(true);
	 				},500);
	 				//console.log(res);
	 			}
	 		})
	 		//console.log(form);
	 	})
		$(document).on('click','.wmp_import_data',function(){
			var m = confirm("Do you want import default data?");
			if(m==true) {
				var data = {'action':'wmp_ajax_import_data'};
				var selector = $(this);
				$.ajax({
					url:wmp_vars.ajax_url,
					'type':'POST',
					data:data,
					beforeSend:function() {
						selector.attr('disabled','disabled');
						selector.find('.spinner').css({'visibility':'visible'});
					},
					success:function(res){
						if(res.status) {
							selector.removeAttr('disabled');
							selector.find('.spinner').css({'visibility':'hidden'});
							$('.wmp-response').addClass('notice notice-success').html('<p>Data has been succssfully imported.</p>');
						}
						console.log(res);
					}

				})
			}
		
		})
	})

})( jQuery );
