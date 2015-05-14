<?
namespace Order;
require_once LIB_DIR.'/DbManager.php';
require_once LIB_DIR.'/Mysql.php';
require_once MODEL_DIR.'/User.php';

define('ORDER_LIST_LIMIT', 30);

function get($id) {

}

function getActiveOrders($currentUser) {
	if ($currentUser['role'] === EXECUTOR_ROLE) {
		return getActiveOrdersForExecutor($currentUser['id']);
	}
	elseif ($currentUser['role'] === CUSTOMER_ROLE) {
		return getActiveOrdersForCustomer($currentUser['id']);
	}
	else {
		// TODO вероятно ошибка?
		return [];
	}
}

function getActiveOrdersForExecutor($executorId, $offset = null) {
	$where = ['is_finished' => 0];
	$orderBy = ['time_created' => 'DESC'];
	return \Mysql\select(_getConnect(), _getTable(), _getColumnList(), $where, $orderBy, ORDER_LIST_LIMIT, $offset);
}

function getActiveOrdersForCustomer($customerId, $offset = null) {
	// select * from orders where is_finished = 0 and author_id = :author_id order by time_created desc
	$where = ['is_finished' => 0, 'author_id' => $customerId];
	$orderBy = ['time_created' => 'DESC'];
	return \Mysql\select(_getConnect(), _getTable(), _getColumnList(), $where, $orderBy, ORDER_LIST_LIMIT, $offset);
}


function _getConnect() {
	return \DbManager\connect('orders');
}

function _getTable() {
	return \DbManager\getTable('orders');
}

function _getColumnList() {
	return [
		'id' => 'id',
		'subject' => 'subject',
		'description' => 'description',
		'author_id' => 'author_id',
		'executor_id' => 'executor_id',
		'time_created' => 'time_created',
		'time_finished' => 'time_finished',
	];
}