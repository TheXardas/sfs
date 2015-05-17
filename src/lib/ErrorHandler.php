<?

namespace ErrorHandler;

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