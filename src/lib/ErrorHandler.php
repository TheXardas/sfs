<?

namespace ErrorHandler;

function isClientError($e) {
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
}