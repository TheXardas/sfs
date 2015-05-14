$(document).ready(function() {
	$('.form').ajaxForm({
		beforeSubmit: function(data, $form, options) {
			$form.find('.form-submit input').prop('disabled', true);
		},
		error: function(data, qwe, ewrt) {
			console.log(data, qwe, ewrt);
			$(this).find('.form-submit input').prop('disabled', false);
		},
		success: function(data, qwe, ewrt) {
			console.log(this);
			$(this).find('.form-submit input').prop('disabled', false);
		}
	});
});