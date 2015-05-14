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
	// TODO detect view type (html vs json)

	echo \View\render($result['view'], $result['ctx']);
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

function redirect($url) {
	header("Location: $url");
	exit;
}

function delegateTo($controller, $action, $params = array()) {
	return run($controller, $action, $params);
}

function error($code) {
	// todo implement
}