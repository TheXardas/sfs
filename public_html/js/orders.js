$(document).ready(function() {
	$(document).on('click', '.orders-list-upload-more', function() {
		var $this = $(this);
		if ($this.hasClass('disabled')) return;
		$this.addClass('disabled');
		$.ajax('/order/list', {
			data: {
				offset: $('.orders-list-item').length
			},
			success: function(data) {
				$this.remove();
				var $data = $(data);
				$('.orders-list').append($data);
				$('.main-content').trigger('ajax.load');
			}
		});
	});
});