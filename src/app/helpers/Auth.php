<?
namespace AuthHelper;

require_once LIB_DIR.'/Session.php';

/**
 * @return bool
 * @throws \Exception
 */
function isLoggedOn() {
	// TODO проверять IP, браузер и прочие подозрительные штуки на предмет session-highjack.
	// TODO хранить хэш от хэша пароля и перепроверять его все время
	$user = \Session\getCurrentUser();
	return $user && $user['id'];
}

/**
 * Создать сессию пользователя на основе логина-пароля
 *
 * @param $login
 * @param $password
 *
 * @return bool|mixed
 */
function login($login, $password) {
	$password = getPasswordHash($password);
	$user = \User\getByLoginAndPass($login, $password);
	if (!$user) {
		return false;
	}
	// Системой логиниться нельзя
	if (!array_key_exists($user['role'], \User\_allowedRoles(true))) {
		return false;
	}

	\Session\set('user_id', $user['id']);
	return $user;
}

/**
 * Вылогиниваемся
 */
function logout() {
	session_destroy();
}

/**
 * Проверяет соответствие пароля бизнес-логике
 * Считаем, что пароль может быть набран латиницей и цифрами
 *
 * @param $password
 *
 * @return bool
 * @throws \Exception
 */
function validatePassword($password) {
	if (!$password) {
		throw new \Exception('Пароль не может быть пустым', 811);
	}

	if (preg_match('|[^a-zA-Z0-9/]|', $password)) {
		throw new \Exception('Пароль должен состоять из строчных или заглавных латинских букв и цифр', 811);
	}
	return true;
}

/**
 * Считаем хэш от пароля для хранения в БД.
 *
 * @param $password
 *
 * @return string
 */
function getPasswordHash($password) {
	return sha1(md5($password));
}