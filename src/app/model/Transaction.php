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

function _getConnect() {
	return \DbManager\connect('transactions');
}

function _getTable() {
	return \DbManager\getTable('transactions');
}

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