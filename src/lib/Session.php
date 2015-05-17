<?
namespace Session;

require_once MODEL_DIR.'/User.php';


/**
 * Открывает сессию. Надо всегда запускать.
 *
 * @return bool
 */
function start() {
	return session_start();
}

/**
 * Возвращает значение из сессии по ключу
 *
 * @param $key
 *
 * @return null
 */
function get($key) {
	if (array_key_exists($key, $_SESSION)) {
		return $_SESSION[$key];
	}
	return null;
}

/**
 * Кладет в сессию значение
 *
 * @param $key
 * @param $value
 */
function set($key, $value) {
	$_SESSION[$key] = $value;
}

/**
 * Закрывает сессию, что приводит к логауту
 *
 * @return bool
 */
function destroy() {
	return session_destroy();
}

/**
 * Возвращает текущего залогиненного пользователя
 *
 * @param bool $reReadFromDb
 *
 * @return mixed|null
 * @throws \Exception
 */
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