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
		mysqli_set_charset($connect, 'utf8');

		$connections[$host] = $connect;
	}
	return $connections[$host];
}

/**
 * Возвращает список уникальных коннектов к серверам по названиям их БД.
 *
 * @param $dbNames
 *
 * @return array
 * @throws \Exception
 */
function getUniqueConnectionsByDbNames($dbNames) {
	$uniqueConnections = [];
	// TODO можно статически закэшировать, но возможно без serialize($dbNames) будет быстрее.
	foreach ($dbNames as $dbName) {
		$host = \Config\get("db.$dbName.host");
		if (!array_key_exists($host, $uniqueConnections)) {
			$uniqueConnections[$host] = connect($dbName);
		}
	}
	return array_values($uniqueConnections);
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
