$(document).ready(function() {
	$(document).on('click', '.orders-list-upload-more', function() {
		var $this = $(this);
		if ($this.hasClass('disabled')) return;
		$this.addClass('disabled');
		$.ajax('/order/list', {
			data: {
				offset: $('.orders-list-item:not(.finished)').length
			},
			success: function(data) {
				$this.remove();
				var $data = $(data);
				$('.orders-list').append($data);
				$('.main-content').trigger('ajax.load');
			}
		});
	});
	$(document).on('ajax.form.success', '.order-work-form.form', function() {
		var $this = $(this);
		$this.parents('.orders-list-item').addClass('finished');
		$this.replaceWith('<div class="form-success">Заказ успешно завершен!</div>');
		updateAccount();
	});
});