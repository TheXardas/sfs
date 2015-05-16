$(document).ready(function() {
	window.nav = {
		go: function(url) {
			if (this.isLocalUrl(url)) {
				this.goLocal(url);
			}
			else {
				this.goExternal(url);
			}
		},

		changeUrl: function(url, title) {
			window.history.pushState({}, title, url);
		},

		isLocalUrl: function(url) {
			if (url[0] === '/') {
				return true;
			}

			return this.getDomainFromUrl(location.href) === this.getDomainFromUrl(url);
		},

		getDomainFromUrl: function(url) {
			var domainRegExp = /https?:\/\/((?:[\w\d]+\.)+[\w\d]{2,})/i;
			return domainRegExp.exec(url)[1];
		},

		goLocal: function(url) {
			// TODO конечно этот контейнер надо куда-то вынести
			var $content = $('.main-content');
			if ($content.hasClass('loading')) return;
			$content.addClass('loading');

			$content.load(url, function(data) {
				// todo - обрабатывать title'ы
				window.nav.changeUrl(url, 'Система абстрактных заказов');
				$content.removeClass('loading');

				$content.trigger('ajax.load', data);
			});
		},

		goExternal: function(url) {
			window.location.href = url;
		}

	};
});


// Перетащить куда-нибудь в скрипты инициализации
$(document).ready(function() {
	// Вешаем обработку кликов по всем ссылкам, и убираем поведение по-умолчанию.
	// К слову, enter тоже сработает.
	$(document).on('click', 'a', function() {
		window.nav.go($(this).attr('href'));
		return false;
	});

	// Запрещено кликать на контент, который перегружаетс
	$(document).on('click', '.main-content.loading', function() {
		return false;
	});
});