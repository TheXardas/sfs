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
	return [
		'id' => 123,
		'name' => 'Василий Пупкин',
		'login' => 'vasyapup',
		'role' => 1,
	];

	return [
		'id' => get('user_id'),
		'name' => get('user_name'),
		'login' => get('user_login'),
		'role' => get('user_role'),
	];
}