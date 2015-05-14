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