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

/**
 * Метод создания заказа
 *
 * @param $subject
 * @param $description
 * @param $price
 * @param null $author_id
 * @param null $time_created
 * @return int|string
 * @throws \Exception
 */
function create($subject, $description, $price, $author_id = null, $time_created = null) {
	$subject = trim($subject);
	if (!$subject) {
		throw new \Exception('Subject cannot be empty', 800);
	}

	$description = trim($description);
	if (!$description) {
		throw new \Exception('Description cannot be empty', 800);
	}

	$price = intval($price);
	if (!$price) {
		throw new \Exception('Price must be specified', 800);
	}
	if ($price < 100) {
		throw new \Exception('Price must be more then 100 rubles', 800);
	}
	if ($price > 100000000) {
		throw new \Exception('Price must be less then 100000000 rubles', 800);
	}

	if (!$author_id) {
		$author_id = \Session\getCurrentUser()['id'];
	}

	if (!$time_created) {
		$time_created = time();
	}

	// TODO validate timestamp
	/*if ($time_created < strtotime('-30 years') || $time_created > strtotime('+30 years')) {
		throw new \Exception('Incorrect time_created');
	}*/


	$id = \Mysql\insert(_getConnect(), _getTable(), [
		'subject' => $subject,
		'description' => $description,
		'author_id' => $author_id,
		'time_created' => $time_created,
		'price' => $price,
	]);

	if (!$id) {
		throw new \Exception('Failed to create order in db');
	}

	return $id;
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
		'is_finished' => 'is_finished',
		'price' => 'price',
	];
}