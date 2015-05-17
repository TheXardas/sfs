$(document).ready(function() {
	$('.form').each(function() {
		onFormLoad(this);
	});

	$('.main-content').on('ajax.load', function() {
		$(this).find('.form').each(function() {
			onFormLoad(this);
		});
	});
});

function onFormLoad(form) {
	var $form = $(form);
	if ($form.hasClass('ajaxified')) return;
	$form.addClass('ajaxified');

	$form.find('input, textarea, select').on({
		change: function() {
			$form.find('.form-error').remove();
		},
		keydown: function() {
			$form.find('.form-error').remove();
		},
		click: function() {
			$form.find('.form-error').remove();
		}
	});

	$form.ajaxForm({
		beforeSubmit: function(data, $form, options) {
			this.$form = $form;

			// Вырубаем кнопку, пока всё грузится
			$form.find('.form-submit input').prop('disabled', true);
		},
		success: function(data, status, jqXHR) {
			var json = {};
			try {
				json = $.parseJSON(data);
			}
			catch (e) {}


			// На результат формы у нас может быть несколько реакций:
			// Редирект на другую страницу

			// Сообщение об успешной работе
			// Ошибка, которую нужно отобразить клиенту в форме:
			if (json.error) {
				// TODO вытащить в отдельный класс
				var $errorMessage = $('<div class="form-error">'+json.error+'</div>');
				$errorMessage.appendTo(this.$form);
				return;
			}
			if (json.result == 1) {
				$form.trigger('ajax.form.success');
			}
			// TODO еще может быть ошибка сервера (fatal), которую тоже надо обработать и предупредить пользователя
		},
		complete: function(jqXHR, status) {
			// Врубаем кнопку назад
			this.$form.find('.form-submit input').prop('disabled', false);
		}
	});
}