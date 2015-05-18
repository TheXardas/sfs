<?
namespace Controller;

require_once LIB_DIR.'/View.php';

/**
 * Метод, который запускает и обрабатывает экшн.
 * После работы именного экшна, запускается view.
 * View может быть как html, так и json, в зависимости от запроса
 *
 * @param $controller
 * @param $action
 * @param array $params
 *
 * @throws \Exception
 */
function process($controller, $action, $params = array()) {
	try {
		$result = run( $controller, $action, $params );
	}
	catch (\Exception $e) {
		$result = \ErrorHandler\processControllerError($e);
	}

	if (!is_array($result)) {
		$result = [];
	}

	if (empty($result['view'])) {
		$result['view'] = "/$controller/$action";
	}

	if (\Request\isPost() && \Request\isAjax() || !empty($result['forceJson'])) {
		if (array_key_exists('ctx', $result)) {
			echo json_encode( $result['ctx'] );
		}
		// TODO ситуация: аякс, но без ответа в экшне. По сути некорректный экшн.
	}
	else {
		// возвращает html

		// Для встроенных виджетов нужно добавить параметр
		$embedded = false;
		if (!empty($params['embedded'])) {
			$embedded = true;
		}
		echo \View\render($result['view'], $result['ctx'], $embedded);
	}
}

/**
 * Запускает экшн контроллера и возвращает результат работы.
 *
 * @param $controller
 * @param $action
 * @param array $params
 *
 * @return array
 * @throws \Exception
 */
function run($controller, $action, $params = array()) {
	$file = CONTROLLER_DIR."/$controller/$action.php";
	if (!$controller || !$action) {
		throw new \Exception('Failed to find action for requested uri');
	}
	if (!file_exists($file)) {
		throw new \Exception(sprintf('Failed to find action %1s:%2s', $controller, $action));
	}

	// Если пользователь залогинен и пытается открыть экшн, который требует авторизации, то выпинчиваем на форму логина.
	// И наоборот, если пытаемся залогиниться, когда уже сессия есть - отсылаем на список заказов.
	if (\AuthHelper\isLoggedOn() && !empty($params['noLoginRequired']) || !\AuthHelper\isLoggedOn() && empty($params['noLoginRequired'])) {
		redirect('/', false, true);
	}
	$result = include $file;

	if (!is_array(($result))) {
		throw new \Exception(sprintf('Action should return an array! %1s does not!', $file));
	}
	return $result;
}

/**
 * Производит редирект
 * Если запрос пришел по аяксу, то добавляет заголовок для клиентского навигатора: "редиректнись"
 *
 * @param $url
 * @param bool $forceAjax - Если true, то призводит редирект в аякс-запросе, а не передает заголовок для навигатора
 * @param bool $refreshBrowser - Говорит навигатору, что надо перезагрузить страницу (обновить меню и т.п.)
 */
function redirect($url, $forceAjax = false, $refreshBrowser = false) {
	if (!\Request\isAjax() || $forceAjax) {
		header("Location: $url");
		exit;
	}

	header("X-REDIRECT-TO-LOCATION: $url");
	if ($refreshBrowser) {
		header('X-REFRESH-BROWSER: 1');
	}
	exit;
}

/**
 * Фильтрует параметры запроса на основе переданных спецификаций
 * Метод для использования в экшнах.
 * Формат спецификаций:
 * array(
 *    'paramName' => ['paramType', 'defaultValue']
 * );
 *
 * @param $definitions
 * @param $params
 *
 * @return array
 * @throws \Exception
 */
function filterParams($definitions, $params) {
	$rawParams = isset($params['raw']) ? $params['raw'] : [];
	$result = [];
	foreach ($definitions as $paramName => $definition) {
		if (!is_array($definition)) {
			throw new \Exception('Param definition should be array');
		}

		if (!isset($definition[0])) {
			throw new \Exception('Empty definition');
		}
		$type = $definition[0];
		$value = NULL;
		if (isset($rawParams[$paramName])) {
			$value = filterParamByType($rawParams[$paramName], $type);
		}
		if ($value === NULL && array_key_exists(1, $definition)) {
			$value = $definition[1];
		}
		$result[] = $value;
	}
	return $result;
}

/**
 * Фильтрует значение в зависимости от его типа
 *
 * @param $value
 * @param $type
 *
 * @return array|int|string
 * @throws \Exception
 * @todo доработать, проверки детские.
 */
function filterParamByType($value, $type) {
	switch ($type) {
		case 'string':
			$value = (string) $value;
			break;
		case 'integer':
		case 'int':
			$value = (int) $value;
			break;
		case 'array':
			// TODO возможность передавать тип значений массива
			$value = (array) $value;
			break;
		default:
			throw new \Exception(sprintf('Unknown parameter type: %1s', $type));
	}

	return $value;
}