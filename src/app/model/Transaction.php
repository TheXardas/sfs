<?
namespace Transaction;

// Константы операций для логов
define('GET_PAYMENT_FROM_OPERATION', 10);
define('GET_PAYMENT_FOR_WORK_OPERATION', 11);

define('PAY_TO_OPERATION', 20);
define('SYSTEM_COMMISSION_OPERATION', 21);
define('PAY_FOR_WORK_OPERATION', 22);
define('TEST_ADD_MONEY_OPERATION', 23);

// TODO надо бы логи по-подробнее делать
/**
 * @param $userId
 * @param $operation
 * @param $amount
 * @param $orderId
 *
 * @return int|string
 * @throws \Exception
 */
function create($userId, $operation, $amount, $orderId) {
	return \Mysql\insert(_getConnect(), _getTable(), [
		'account_id' => $userId,
		'operation' => $operation,
		'amount' => $amount,
		'order_id' => $orderId,
		'time_created' => time(),
	]);
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
	return 'transactions';
}

/**
 * Возвращает список колонок, которые можно выбирать из бд для данной модели
 *
 * @return array
 */
function _getColumnList() {
	return [
		'id' => 'id',
		'account_id' => 'account_id',
		'operation' => 'operation',
		'amount' => 'amount',
		'order_id' => 'order_id',
		'time_created' => 'time_created',
	];
}