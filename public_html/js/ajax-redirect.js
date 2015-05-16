/**
 В этом файле лежит код, который отвечает за редирект в ajax-ответе
 */
(function(window, document, $) {
	var $document = $(document);
	/**
	 * Данный обработчик смотрит на заголовки ответа, и если
	 * присутствует X-Redirect-To-Location, то перенаправляет браузер клиента на
	 * url находящийся в этом заголовке
	 */
	$document.on('ajaxSuccess, ajaxComplete', function( event, jqXHR ) {
		if (jqXHR.isRedirect()) {
			window.nav.go(jqXHR.getTargetUrl());
			//document.location.href = jqXHR.getTargetUrl();
		}
	} );

	/**
	 * В этом событии мы расширяем объект jqXHR дополнительными методами,
	 * добавляющие управление редиректом.
	 */
	$document.on('ajaxSend', function(event, jqXHR, ajaxOptions) {
		jqXHR.isRedirect = function() {
			return ! this['redirectionDisabled'] && this.getResponseHeader && !! this.getResponseHeader('x-redirect-to-location');
		};

		jqXHR.getTargetUrl = function() {
			return this['redirectionDisabled'] ? '' : this.getResponseHeader && this.getResponseHeader('x-redirect-to-location');
		};

		jqXHR.disableRedirection = function() {
			this['redirectionDisabled'] = true;
		};

		jqXHR.enableRedirection = function() {
			this['redirectionDisabled'] = false;
		}
	});
})(window, window.document, window.jQuery);