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
	elseif (isNotFoundError($e)) {
		//http_response_code(404);
		//throw $e;
		\Controller\redirect('/');
	}
	else {
		throw $e;
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

/**
 * @param \Exception $e
 *
 * @return bool
 */
function isNotFoundError(\Exception $e)
{
	return $e->getCode() === 404;
}