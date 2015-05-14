$(document).ready(function() {
	$('.form').ajaxForm({
		beforeSubmit: function(data, $form, options) {
			this.$form = $form;
			$form.find('.form-submit input').prop('disabled', true);
		},
		error: function(data, qwe, ewrt) {
			this.$form.find('.form-submit input').prop('disabled', false);
		},
		success: function(data, qwe, ewrt) {
			this.$form.find('.form-submit input').prop('disabled', false);
		}
	});
});