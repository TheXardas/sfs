<?
namespace Session;

require_once MODEL_DIR.'/User.php';

function start() {
	return session_start();
}

function get($key) {
	if (array_key_exists($key, $_SESSION)) {
		return $_SESSION[$key];
	}
	return null;
}

function set($key, $value) {
	$_SESSION[$key] = $value;
}

function destroy() {
	return session_destroy();
}

function getCurrentUser($reReadFromDb = false) {
	static $currentUser = null;
	$id = get('user_id');
	if ($id && (!$currentUser || $reReadFromDb)) {
		$currentUser = \User\get( $id );
		if (!$currentUser) {
			throw new \Exception('Ошибка авторизации');
		}
	}

	return $currentUser;
}