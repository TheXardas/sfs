<?
namespace DbManager;
require_once LIB_DIR.'/Config.php';

/**
 * Производит, кэширует и возвращает коннект к базе данных
 *
 * @param $dbName
 *
 * @return mixed
 * @throws \Exception
 */
function connect($dbName) {
	static $connections = [];
	$host = \Config\get("db.$dbName.host");
	if (!isset($connections[$host])) {
		$user = \Config\get("db.$dbName.user");
		$pass = \Config\get("db.$dbName.pass");
		$connect = mysqli_connect( $host, $user, $pass );
		if (mysqli_connect_errno() !== 0) {
			throw new \Exception(sprintf('Failed connecting to database %1', $dbName));
		}
		$connections[$host] = $connect;
	}
	return $connections[$host];
}

/**
 * Возвращает название таблички для выборки (с БД).
 *
 * @param $modelName
 * @return string
 * @throws \Exception
 */
function getTable($modelName) {
	static $tables = [];
	$dbName = \Config\get("db.$modelName.name");
	$tableName = $modelName;
	return "$dbName.$tableName";
}
