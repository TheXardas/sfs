<?

namespace Controller;

function run($controller, $action) {
	return require_once CONTROLLER_DIR."/$controller/$action.php";
}