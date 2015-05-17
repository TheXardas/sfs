<?
namespace User;

require_once LIB_DIR.'/Mysql.php';
require_once LIB_DIR.'/DbManager.php';
require_once HELPER_DIR.'/Auth.php';

define('EXECUTOR_ROLE', 0);
define('CUSTOMER_ROLE', 1);
define('SYSTEM_ROLE', 2);

/**
 * @param $login
 * @param $password
 *
 * @return mixed
 */
function getByLoginAndPass($login, $password) {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['login' => $login, 'password' => $password]);
}
/**
 * @param $login
 *
 * @return mixed
 */
function getByLogin($login) {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['login' => $login]);
}

/**
 * Возвращает системного пользователя, которому начисляется коммиссия за операции
 *
 * @param bool $forUpdate
 *
 * @return mixed
 */
function getSystemUser($forUpdate = false) {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['role' => SYSTEM_ROLE], [], $forUpdate);
}

/**
 *
 * @param array $user
 *
 * @return bool
 * @throws \Exception
 */
function canCreateOrders(array $user = null) {
	if ($user === NULL) {
		$user = \Session\getCurrentUser();
	}
	return $user['role'] === CUSTOMER_ROLE;
}

/**
 * Может ли пользователь работать над заказами
 *
 * @param array $user
 *
 * @return bool
 */
function canWorkOnOrders(array $user = null) {
	if ($user === NULL) {
		$user = \Session\getCurrentUser();
	}
	return $user['role'] === EXECUTOR_ROLE;
}

/**
 * @param int $id
 * @param bool $forUpdate
 *
 * @return mixed
 */
function get($id, $forUpdate = false) {
	$user = \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['id' => $id], [], $forUpdate);
	if ($user) {
		$user['role'] = (int) $user['role'];
	}
	return $user;
}

function setMoney($userId, $money) {
	// TODO написать в лог, что произошло.
	return \Mysql\update(_getConnect(), _getTable(), ['money' => $money], ['id' => $userId]);
}

/**
 * Достает пользователей по массиву id.
 *
 * @param int[] $ids
 * @return array
 */
function getUsersByIds(array $ids = [], $forUpdate = false)
{
	return \Mysql\select(_getConnect(), _getTable(), _getColumnList(), [
		'id' => $ids
	], [], NULL, NULL, true, $forUpdate);
}

/**
 * Метод создания Пользователя
 *
 * @param $name
 * @param $login
 * @param $password
 * @param $passwordConfirm
 * @param $role
 *
 * @throws \Exception
 * @return int|string
 */
function create($name, $login, $password, $passwordConfirm, $role) {
	$name = trim($name);
	if (!$name) {
		throw new \Exception('Представьтесь, пожалуйста', 813);
	}

	$login = trim($login);
	if (!$login) {
		throw new \Exception('Обязательно нужно выбрать Логин', 812);
	}
	if (preg_match('|[^a-zA-Z0-9/]|', $login)) {
		throw new \Exception('Логин должен состоять из строчных или заглавных латинских букв и цифр', 812);
	}

	if ($password !== $passwordConfirm) {
		throw new \Exception('Пароли не совпадают', 813);
	}

	$ok = \AuthHelper\validatePassword($password);
	if (!$ok) {
		// todo с одной стороны - надо бы нам узнать, какой пользователь попытался ввести пароль.
		// А с другой - неэтично
		throw new \Exception(sprintf('Something wrong with user password! %1', $ok));
	}
	$password = \AuthHelper\getPasswordHash($password);

	if (!array_key_exists($role, _allowedRoles())) {
		throw new \Exception('User must to have a role');
	}

	$existingUser = \User\getByLogin($login);
	if ($existingUser) {
		throw new \Exception('Такой логин уже занят', 814);
	}

	$id = \Mysql\insert(_getConnect(), _getTable(), [
		'name' => $name,
		'login' => $login,
		'password' => $password,
		'role' => $role,
	]);

	if (!$id) {
		throw new \Exception('Failed to create user in db');
	}

	return $id;
}


/**
 * @return mixed
 * @throws \Exception
 */
function _getConnect() {
	return \DbManager\connect(_getDbName());
}

/**
 * @return string
 */
function _getTable() {
	return \DbManager\getTable(_getDbName());
}

/**
 * @return string
 */
function _getDbName() {
	return 'users';
}

/**
 * @return array
 */
function _getColumnList() {
	return [
		'id' => 'id',
		'name' => 'name',
		'login' => 'login',
		// пароль выбирать нельзя, даже в хэше
		// 'password' => 'password',
		'role' => 'role',
		'money' => 'money',
	];
}

/**
 * @param bool $fromForm
 *
 * @return array
 */
function _allowedRoles($fromForm = true) {
	$result = [
		EXECUTOR_ROLE => EXECUTOR_ROLE,
		CUSTOMER_ROLE => CUSTOMER_ROLE,
	];
	if (!$fromForm) {
		$result[SYSTEM_ROLE] = SYSTEM_ROLE;
	}
	return $result;
}