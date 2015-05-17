<?
namespace Router;

function getActionFromUri($uri)
{
	$routes = getRoutesConfig();

	// Убираем get-параметры и #-hash
	$uri = strtok($uri, '?');
	// Убираем с обоих сторон "/"
	$uri = trim($uri, "/");

	// Сначала проверяем, может быть у нас кастомный роут, по ключу.
	if (!empty($routes[$uri])) {
		$result = $routes[$uri];
		$requestParams = \Request\getParams();
		if ($requestParams) {
			$result['params']['raw'] = $requestParams;
		}
		if (!isset($result['params'])) {
			$result['params'] = [];
		}
		return $result;
	}


	// Парсим uri
	// В случае автоматической маршрутизации не должно быть никаких символов, кроме букв и "/".
	// TODO когда надо будет открыть карточку конкретной сущности (с id), тогда это правило надо будет расширить
	if (preg_match('|[^a-zA-Z/]|', $uri)) {
		return [
			'controller' => '',
			'action' => '',
			'params' => [],
		];
	}

	// Для автоматической маршрутизации считаем, что маршрут имеет вид $controller/$action
	$controller = '';
	$action = '';
	$params = [];
	if ($uri) {
		list($controller, $action) = explode( '/', $uri, 2 );
	}

	// TODO вытащить в конфиг экшн по умолчанию
	if (!$controller) {
		if (\AuthHelper\isLoggedOn()) {
			$controller = 'order';
			$action = 'list';
		}
		else {
			return getActionFromUri('/login');
		}
	}

	$result = [
		'controller' => $controller,
		'action' => $action,
		'params' => $params,
	];

	$requestParams = \Request\getParams();
	if ($requestParams) {
		$result['params']['raw'] = $requestParams;
	}

	return $result;
}

/**
 * @return mixed
 */
function getRoutesConfig() {
	static $config = false;
	if ($config === false) {
		$config = include CONFIG_DIR . '/routes.php';
	}
	return $config;
}