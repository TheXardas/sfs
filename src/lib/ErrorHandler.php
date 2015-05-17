<?
namespace ErrorHandler;

// Не уверен, позволено ли считать использование исключений - ООП. Но в целом не так и долго пределать, если что.

/**
 * Конвертирует Exception контроллера в возвращаемый ответ
 *
 * @param \Exception $e
 *
 * @return mixed
 */
function processControllerError(\Exception $e) {
	if (isClientError($e)) {
		$result['ctx']['error'] = $e->getMessage();;
	}
	else {
		// TODO печатать ошибку в лог.
		$result['ctx']['error'] = 'С сервером что-то случилось! Мы уже в курсе, скоро починим!';
		// TODO убрать на продакшне
		$result['ctx']['error'] .= $e->getMessage();
	}
	return $result;
}

/**
 * Можно ли выводить ошибку клиенту
 *
 * @param \Exception $e
 *
 * @return bool
 */
function isClientError(\Exception $e) {
	$code = $e->getCode();

	// Необработанные или обычные ошибки
	if (0 === $code) {
		return false;
	}

	$group = (int) ($code / 100);
	// Считаем, что ошибки от 800 до 899 можно показывать клиенту.
	if ($group === 8) {
		return true;
	}

	return false;
}