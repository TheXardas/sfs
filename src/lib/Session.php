<?
namespace Session;

function start() {
	return session_start();
}

function get($key) {
	return $_SESSION[$key];
}

function set($key, $value) {
	$_SESSION[$key] = $value;
}

function destroy() {
	return session_destroy();
}

function getCurrentUser() {
	static $currentUser = null;
	$id = get('user_id');
	$id = 123;
	/*return [
		'id' => 123,
		'name' => 'Василий Пупкин',
		'login' => 'vasyapup',
		'role' => 0,
		'money' => 0,
	];
*/
	if (!$currentUser) {
		$currentUser = \User\get( $id );
	}
	if (!$currentUser) {
		throw new \Exception('Ошибка авторизации');
	}

	return $currentUser;
}