$( function() {
	// Устанавливаем обработчик unload
	var windowUnloadHandler = function() {
		// Снимаем обработчик, чтобы он не выполнился дважды
		$( window ).unbind( 'unload', windowUnloadHandler );
		// Снимаем обработчики ошибок AJAX
		$( document ).unbind( 'ajaxError' );
		// Снимаем обработчики успеха AJAX
		$( document ).unbind( 'ajaxSuccess' );
	};
	// Пытаемся понять, есть ли поддержка beforeunload
	var test = document.createElement('div');
	test.setAttribute('onbeforeunload', '');
	if( typeof window.onbeforeunload != 'undefined' || typeof test.onbeforeunload == 'function' ) {
		window.onbeforeunload = windowUnloadHandler;
	}
	$( window ).unload( windowUnloadHandler );

	// Устанавливаем обработчик ошибок AJAX
	$( document ).on('ajaxError', function( evt, xhr, settings, exception ) {
		if ( xhr.status == 401 ) {
			$( document ).trigger('userLoggedOut', 'ajaxError401');
			return true;
		}
		// Если это ошибка UserLive - игонорируем
		if ( settings && settings.url && settings.url.match( /^\/chk_evnts/ ) ) {return;}
		var error = {
			message: '',
			code: ''
		};
		// Теперь производим унификацию сообщения об ошибке, чтобы передать ее дальше в удобном формате
		// Если это некорректный ответ сервера, например ошибочный JSON - обрабатываем
		if ( exception ) {
			error = {
				code: 0,
				message: exception.message || exception.toString(),
				url: settings.url,
				xhr: xhr
			}
		} else {
			try {
				// Если это ошибка сервера - отображаем ее
				// TODO кажется сюда скрипт никогда не заходит, разобраться.
				error.code = 1;
				error.message = xhr.statusText + xhr.responseText;
				if ( error.message == '' ) {
					throw new Error();
				}
				if( error.message.indexOf( "<?xml" ) >= 0 ) {
					// У нас ошибка на сервере и мы получили в качестве ответа стандартную страницу ошибки
					// Выделяем stacktrace
					var stacktrace = error.message.indexOf("<?xml") >= 0 ?
						error.message.substring(
							0,
							error.message.indexOf("<?xml")
						) : '';
					// Стили, они нам нужны чтобы все было красиво
					var style = error.message.substring(
						error.message.indexOf("<style"),
						error.message.indexOf("</style>") + 8
					);
					// Собственно сообщение об ошибке сервера
					var serverError = error.message.substring(
						error.message.indexOf("<body>") + 6,
						error.message.indexOf("</body>")
					);
					// Объединяем все три части и получаем страницу ошибки, пригодную для отображения
					error.stacktrace = stacktrace;
					error.message = stacktrace + style + serverError;
				}
			} catch ( e ) {
				// Если это обрыв связи - выводим сообщение о проблемах с соединением
				error.code = 2;
				error.message = 'Connection closed by remote server';
			}
		}
		// Объект ошибки сформирован, дергаем событие на <body>,
		if( window.onbeforeunload ) {
			// Событие beforeunload поддерживается, дергаем обработчик на body сразу
			$( document ).trigger( 'ajax.error', error );
		} else {
			// Дергаем с задержкой, чтобы успел отработать unload
			window.setTimeout( function() {
				$( document ).trigger( 'ajax.error', error );
			}, 3000 );
		}
	} );

	// Устанавливаем менеджер системных нотификаций
	$( document ).on( 'ajax.error', function( e, error ) {
		var message;
		var debug = true;
		var jsError = function() {
			return '<div class="global-error">' +
				'<div class="global-error-inner">' +
				'<div class="global-error-header">' +'JavaScript error'+ '</div>' +
				'<div class="global-error-message">' +'We are greatly sorry but something goes wrong.'+
				'</div>' +
				'</div>' +
				'</div>';
		};
		switch ( error.code ) {
			case 2:
				// Это обрыв соединения.выводим красивое сообщение
				message =
					'<div class="global-error">' +
						'<div class="global-error-inner">' +
						'<div class="global-error--header">' + 'Connection closed by remote server' + '</div>' +
						'<div class="global-error-message">' +
						'<ul>' +
						'<li>' + 'Make sure your Internet connection is active and check whether other applications that rely on the same connection are working.' + '</li>' +
						'<li>' + 'Check that the setup of any Internet security software is correct and does not interfere with ordinary web browsing.' + '</li>' +
						'<li>' + 'If you are behind a firewall on a Local Area Network and think this may be causing problems, talk to your systems administrator.' + '</li>' +
						'</ul>' +
						'</div>' +
						'</div>' +
						'</div>';
				break;
			default:
				// Мы не знаем, что это за ошибка, возможно ее код забыл здесь прописать разработчик
				message = jsError();
				break;
		}

		// пишем в консоль для удобной отладки
		if ( debug && console )
		{
			var errorOutput = new Array(
				'Status: ' + (error.xhr ? error.xhr.status : ''),
				'URL: ' + error.url,
				'Message: ' + error.message
			);
			var responseText = error.xhr ? error.xhr.responseText : null;
			if ( responseText ) {
				// Генерация сообщения с трейсом в консоль.
				if ( error.xhr.getResponseContentType() === 'application/json' ) {
					var responseData = $.parseJSON(responseText);
					if (responseData['exceptions']) {
						$.each(responseData['exceptions'], function(index, oneException) {
							errorOutput.push(oneException['message']+' ('+oneException['class']+')');
							if (oneException['trace']) {
								$.each(oneException['trace'], function(index, tRow) {
									if (tRow['class'] || tRow['function']) {
										errorOutput.push('  '+tRow['class']+tRow['type']+tRow['function']);
									}
									if (tRow['file'] && tRow['line']) {
										errorOutput.push('  '+tRow['file']+':'+tRow['line']);
									}

									errorOutput.push('');
								});
							}
							errorOutput.push('');
						});
					}
				} else {
					errorOutput.push( 'Response content type: ' + error.xhr.getResponseContentType() );
				}
			}

			console.log( errorOutput.join( "\n" ) );
		}

		// TODO а здесь вероятно надо отображать красивый модальный бабл на весь экран - произошла ошибка.
		// Но только если аякс-запрос был не из формы.
		// Т.е. это надо навязываться на систему событий.
	} );
} );