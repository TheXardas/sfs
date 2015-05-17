<?
namespace DbTransaction;

require_once LIB_DIR.'/DbManager.php';

// TODO для разбирательств надо обязательно написать логи - что начали, что закончили, а что нет.
// TODO подумать над альтернативой глобальных транзакций (мемкеш не очень подходит, т.к. скрипт может упасть)

/**
 * Начать кросс-серверную транзакцию
 *
 * @param array $dbNames
 *
 * @return string
 * @throws \Exception
 */
function beginTransaction(array $dbNames) {
	$transactionId = _getTransactionId();

	$connections = \DbManager\getUniqueConnectionsByDbNames($dbNames);
	queryTransactionCommand($connections, 'XA START', $transactionId);
	return $transactionId;
}

/**
 * Отменить кросс-серверную транзакцию
 *
 * @param array $dbNames
 * @param $transactionId
 *
 * @throws \Exception
 */
function rollback(array $dbNames, $transactionId) {
	$connections = \DbManager\getUniqueConnectionsByDbNames($dbNames);
	queryTransactionCommand($connections, 'XA END', $transactionId);
	queryTransactionCommand($connections, 'XA ROLLBACK', $transactionId);
}

/**
 * Подтвердить кросс-серверную транзакцию
 *
 * @param array $dbNames
 * @param $transactionId
 *
 * @throws \Exception
 */
function commit(array $dbNames, $transactionId) {
	$connections = \DbManager\getUniqueConnectionsByDbNames($dbNames);
	queryTransactionCommand($connections, 'XA END', $transactionId);
	queryTransactionCommand($connections, 'XA PREPARE', $transactionId);
	queryTransactionCommand($connections, 'XA COMMIT', $transactionId);
}

/**
 * Выполняет транзакционную команду для пачки коннектов
 *
 * @param array $connections
 * @param $command
 * @param $transactionId
 *
 * @throws \Exception
 */
function queryTransactionCommand(array $connections, $command, $transactionId)
{
	foreach ($connections as $connection) {
		\Mysql\_query( $connection, "$command '$transactionId'" );
	}
}

/**
 * Генерирует уникальный ключ для транзакции
 *
 * @return string
 * @throws \Exception
 */
function _getTransactionId() {
	$currentUser = \Session\getCurrentUser();
	$transactionId = uniqid($currentUser['id'], true);
	// TODO сюда можно еще для надежности добавлять запускаемый экшн, и много чего другого
	return $transactionId;
}