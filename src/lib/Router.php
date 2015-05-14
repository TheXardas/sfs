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
		return $routes[$uri];
	}


	// Парсим uri
	// В случае автоматической маршрутизации не должно быть никаких символов, кроме букв и "/".
	if (preg_match('|[^a-zA-Z/]|', $uri)) {
		return [
			'controller' => '',
			'action' => '',
			'params' => [],
		];
	}

	// Для автоматической маршрутизации считаем, что маршрут имеет вид $controller/$action
	list($controller, $action) = explode('/', $uri, 2);

	// TODO реагировать на авторизацию
	if (!$controller) {
		$controller = 'order';
		$action = 'list';
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