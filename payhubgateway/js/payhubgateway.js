$(document).ready(function(){
	$("#ph_cvv_help").fancybox();

	// el must be a jquery object and exist
	function is_valid_object(el) {
		if(el === null || typeof el === 'undefined' || ! el instanceof jQuery) return false;
		return true;
	}

	function apply_error_state(el) {
		if( ! is_valid_object(el)) return false;

		el.addClass('error-state');
		el.parents('div.form-group').find('span.error-state').show();
		el.focus();
	}

	function remove_error_state(el) {
		if( ! is_valid_object(el)) return false;

		el.removeClass('error-state');
		el.parents('div.form-group').find('span.error-state').hide();
	}

	function validate_credit_card(cardnum, cardtype) {
		if(typeof validateCC != 'undefined') {
			return validateCC(cardnum, cardtype)
		}
		else { //let it pass for a server side check
			return true;
		}
	}

	function validate_exp_date(em, ey) {
		var curr_date = new Date();
	 	var cm = curr_date.getMonth() + 1;
	 	var cy = curr_date.getFullYear();

	 	if(ey < cy) return false;
	 	if(ey == cy && em < cm) return false;
	 	return true;
	}

	function validate_cvv(cvv) {
		if( ! cvv.match(/^\d{3,4}$/)) return false;
		return true
	}

	$('.form-control').change(function() {
		remove_error_state($(this));
	});

	$('#submit_payment_btn').click(function() {
		if($('#ph_name_on_card').val().trim() == '') {
			apply_error_state($('#ph_name_on_card'));
			return false;
		} 
		else if( ! validate_credit_card($('#ph_card_num').val(), $('#ph_card_type').val())) {
			apply_error_state($('#ph_card_num'));
			return false;			
		} 
		else if( ! validate_exp_date($('#ph_exp_date_m').val(), $('#ph_exp_date_y').val())) {
			apply_error_state($('#ph_exp_date_m'));
			apply_error_state($('#ph_exp_date_y'));
			return false;			
		} 
		else if( ! validate_cvv($('#ph_card_cvv').val())) {
			apply_error_state($('#ph_card_cvv'));
			return false;
		} 
		else {
			$('#payhubgateway_form').submit();
		}
	});
})