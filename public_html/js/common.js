$(document).ready(function() {
	$('.form').ajaxForm({
		beforeSubmit: function(data, $form, options) {
			this.$form = $form;
			$form.find('.form-submit input').prop('disabled', true);
		},
		error: function(data) {
			alert(data.error);
			this.$form.find('.form-submit input').prop('disabled', false);
		},
		success: function(data) {
			data = JSON.parse(data);
			if (data.error) {
				return this.error(data);
			}
			this.$form.find('.form-submit input').prop('disabled', false);
		}
	});
});