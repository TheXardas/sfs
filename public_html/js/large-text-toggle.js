$(document).on('click', '.large-text-toggler', function() {
	var $this = $(this);
	var $text = $this.prev('.large-text');
	var isShowing = $text.hasClass('cutted');
	if (isShowing) {
		$text.switchClass('cutted', '', 200)
	}
	else {
		$text.switchClass('', 'cutted', 200)
	}
	var label = isShowing ? 'Спрятать текст' : 'Показать полный текст';
	$this.find('.large-text-toggler-label').text(label);
	// Надо проскроллиться до карточки, на случай, если текст был очень длинный
	if (!isShowing && $(window).scrollTop() > $text.offset().top - 100) {
		$('html, body').scrollTop($text.offset().top - 100);
	}
});

$(document).ready(function() {
	attachLargeTextTogglers($(document));

	$('.main-content').on('ajax.load', function() {
		attachLargeTextTogglers($('.main-content'));
	});
});

function attachLargeTextTogglers($el) {
	$('.large-text', $el).each(function() {
		var $this = $(this);
		if ($this.hasClass('large-text-attached')) return;
		$this.addClass('large-text-attached');

		if ($this.text() && $this.height() > 160) {
			$this.after('<span class="large-text-toggler"><span class="large-text-toggler-label clickable">Показать полный текст</span></div>');
			$this.addClass('cutted');
		}
	})
}