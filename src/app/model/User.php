<?
namespace User;

define('EXECUTOR_ROLE', 0);
define('CUSTOMER_ROLE', 1);
define('SYSTEM_ROLE', 2);

/**
 * Возвращает системного пользователя, которому начисляется коммиссия за операции
 * @return mixed
 */
function getSystemUser() {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['role' => SYSTEM_ROLE], []);
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

function get($id) {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['id' => $id]);
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
function getUsersByIds(array $ids = [])
{
	return \Mysql\select(_getConnect(), _getTable(), _getColumnList(), [
		'id' => $ids
	], [], NULL, NULL, true);
}

/**
 * Метод создания Пользователя
 *
 * @param $name
 * @param $login
 * @param $password
 * @param $role
 *
 * @throws \Exception
 * @return int|string
 */
function create($name, $login, $password, $role) {
	$name = trim($name);
	if (!$name) {
		throw new \Exception('Представьтесь, пожалуйста', 800);
	}

	$login = trim($login);
	if (!$login) {
		throw new \Exception('Обязательно нужно выбрать Логин', 800);
	}

	$password = trim($password);
	if (!$password) {
		throw new \Exception('Пароль не может быть пустым', 800);
	}
	if (!array_key_exists($role, _allowedRoles())) {
		throw new \Exception('User must to have a role');
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


function _getConnect() {
	return \DbManager\connect('users');
}

function _getTable() {
	return \DbManager\getTable('users');
}

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

function _allowedRoles() {
	return [
		EXECUTOR_ROLE => EXECUTOR_ROLE,
		CUSTOMER_ROLE => CUSTOMER_ROLE,
		SYSTEM_ROLE => SYSTEM_ROLE,
	];
}