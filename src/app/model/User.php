<?
namespace User;

require_once LIB_DIR.'/Mysql.php';
require_once LIB_DIR.'/DbManager.php';
require_once HELPER_DIR.'/Auth.php';

define('EXECUTOR_ROLE', 0);
define('CUSTOMER_ROLE', 1);
define('SYSTEM_ROLE', 2);

/**
 * Получает пользователя по его логину-паролю
 *
 * @param $login
 * @param $password
 *
 * @return mixed
 */
function getByLoginAndPass($login, $password) {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['login' => $login, 'password' => $password]);
}
/**
 * Получает пользователя по логину
 *
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
 * @todo на этом запросе можно сэкономить, если закэшировать id пользователя. Он не меняется никогда вообще
 * @param bool $forUpdate
 *
 * @return mixed
 */
function getSystemUser($forUpdate = false) {
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['role' => SYSTEM_ROLE], [], $forUpdate);
}

/**
 * Может ли переданный пользователь создавать заказы
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
 * Вытащить пользователя из БД
 * Конвертирует роль в (int) для облегчения строготипизированной проверки
 *
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

/**
 * Выставляет пользователю количество денег на счету
 *
 * @param $userId
 * @param $money
 *
 * @return int
 * @throws \Exception
 */
function setMoney($userId, $money) {
	return \Mysql\update(_getConnect(), _getTable(), ['money' => $money], ['id' => $userId]);
}

/**
 * Достает пользователей по массиву id.
 *
 * @param int[] $ids
 * @return array
 */
function getByIds(array $ids = [], $forUpdate = false)
{
	$users = \Mysql\select(_getConnect(), _getTable(), _getColumnList(), [
		'id' => $ids
	], [], NULL, NULL, true, $forUpdate);
	foreach ($users as $key => $user) {
		$users[$key]['role'] = (int) $user['role'];
	}
	return $users;
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
		throw new \Exception(sprintf('Something wrong with user password! %1s', $ok));
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
 * Возвращает коннект к БД для текущей модели
 *
 * @return mixed
 * @throws \Exception
 */
function _getConnect() {
	return \DbManager\connect(_getDbName());
}

/**
 * Возвращает название таблицы БД с текущей моделью для использования в запросах
 *
 * @return string
 */
function _getTable() {
	return \DbManager\getTable(_getDbName());
}

/**
 * Возвращает название БД для текущей модели
 *
 * @return string
 */
function _getDbName() {
	return 'users';
}

/**
 * Возвращает список колонок, которые можно выбирать из бд для данной модели
 *
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
 * Список разрешенных ролей.
 *
 * @todo в базе не помешает триггер на проверку этого значения
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