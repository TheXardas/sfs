<?
namespace Controller;

require_once LIB_DIR.'/View.php';

function process($controller, $action, $params = array()) {
	$result = run($controller, $action, $params);

	// TODO возможно стоит это эксепшном накрыть
	if (!is_array($result)) {
		$result = [];
	}

	if (empty($result['view'])) {
		$result['view'] = "/$controller/$action";
	}

	if (\Request\isPost() && \Request\isAjax()) {
		// возвращаем json
		echo json_encode($result);
	}
	else {
		// возвращает html
		$embedded = false;
		if (!empty($params['embedded'])) {
			$embedded = true;
		}
		echo \View\render($result['view'], $result['ctx'], $embedded);
	}
}

function run($controller, $action, $params = array()) {
	$file = CONTROLLER_DIR."/$controller/$action.php";
	if (!$controller || !$action) {
		throw new \Exception('Failed to find action for requested uri');
	}
	if (!file_exists($file)) {
		throw new \Exception(sprintf('Failed to find action %1%:%2%', $controller, $action));
	}
	return include $file;
}

function redirect($url, $forceAjax = false) {
	if (!\Request\isAjax() || $forceAjax) {
		header("Location: $url");
		exit;
	}

	header("X-REDIRECT-TO-LOCATION: $url");
	exit;
}

function delegateTo($controller, $action, $params = array()) {
	return run($controller, $action, $params);
}

function error($code) {
	// todo implement
}

function filterParams($definitions, $params) {
	$rawParams = $params['raw'];
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

function filterParamByType($value, $type) {
	switch ($type) {
		case 'string':
			$value = (string) $value;
			break;
		case 'int':
			$value = (int) $value;
			break;
		case 'array':
			$value = (array) $value;
			break;
		default:
			throw new \Exception(sprintf('Unknown parameter type: %1', $type));
	}

	return $value;
}