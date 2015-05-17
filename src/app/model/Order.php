<?
namespace Order;
require_once LIB_DIR.'/DbManager.php';
require_once LIB_DIR.'/Mysql.php';
require_once MODEL_DIR.'/User.php';
require_once MODEL_DIR.'/Bank.php';

define('ORDER_LIST_LIMIT', 5);

function get($id) {
	if (!$id) {
		throw new \Exception('ID required!');
	}
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['id' => $id]);
}

function work($orderId, array $currentUser) {
	// TODO залочку, конечно, напишем еще
	// START LOCK

	if (!\User\canWorkOnOrders($currentUser)) {
		throw new \Exception('Только исполнители могут работать над заказами.', 802);
	}

	$order = get($orderId);
	if (!$order) {
		throw new \Exception('No order with such id exist!');
	}

	if ($order['is_finished']) {
		throw new \Exception('Этот заказ уже завершен.', 801);
	}

	// Завершаем заказ
	$result = finishOrder($orderId, $currentUser['id']);
	if (!$result) {
		throw new \Exception('Failed finishing order!');
	}

	// Вычитаем со счета клиента
	$customer = \User\get($order['author_id']);
	\Bank\getPaymentFrom($customer, $order['price']);

	// Пишем на счет системы комиссию
	$systemUser = \User\getSystemUser();
	$commission = round(\Bank\getSystemCommission() * $order['price']);
	\Bank\payTo($systemUser, $commission);

	// Платим работнику гонорар
	$executorPayment = $order['price'] - $commission;
	// Выбираем из базы текущего пользователя, чтобы точно знать его счет в рамках этой транзакции
	$executor = \User\get($currentUser['id']);
	\Bank\payTo($executor, $executorPayment);

	// END LOCK
}

function finishOrder($orderId, $executorId) {
	return \Mysql\update(_getConnect(), _getTable(), [
		'is_finished' => 1,
		'executor_id' => 1,
		'time_finished' => time(),
	], [
		'id' => $orderId,
	]);
}

function getActiveOrders($currentUser, $offset = null) {
	if ($currentUser['role'] === EXECUTOR_ROLE) {
		return getActiveOrdersForExecutor($currentUser['id'], $offset);
	}
	elseif ($currentUser['role'] === CUSTOMER_ROLE) {
		return getActiveOrdersForCustomer($currentUser['id'], $offset);
	}
	else {
		// TODO вероятно ошибка?
		return [];
	}
}

function getActiveOrdersCount($currentUser) {
	if ($currentUser['role'] === EXECUTOR_ROLE) {
		return getActiveOrdersCountForExecutor($currentUser['id']);
	}
	elseif ($currentUser['role'] === CUSTOMER_ROLE) {
		return getActiveOrdersCountForCustomer($currentUser['id']);
	}
	else {
		// TODO вероятно ошибка?
		return 0;
	}
}

function getActiveOrdersForExecutor($executorId, $offset = null) {
	$where = ['is_finished' => 0];
	$orderBy = ['time_created' => 'DESC'];
	return getOrders($where, $orderBy, ORDER_LIST_LIMIT, $offset);
}

function getActiveOrdersCountForExecutor($executorId) {
	$where = ['is_finished' => 0];
	return getOrdersCount($where);
}

function getActiveOrdersForCustomer($customerId, $offset = null) {
	$where = ['is_finished' => 0, 'author_id' => $customerId];
	$orderBy = ['time_created' => 'DESC'];
	return getOrders($where, $orderBy, ORDER_LIST_LIMIT, $offset);
}

function getActiveOrdersCountForCustomer($customerId) {
	$where = ['is_finished' => 0, 'author_id' => $customerId];
	return getOrdersCount($where);
}

function getOrders($where, $orderBy = null, $limit = null, $offset = null) {
	$orders = \Mysql\select(_getConnect(), _getTable(), _getColumnList(), $where, $orderBy, $limit, $offset);
	if ($orders) {
		// Филлим авторов заказов
		$userIds = [];
		// TODO use array_column()
		foreach ($orders as $order) {
			$userIds[] = $order['author_id'];
		}

		$users = \User\getUsersByIds($userIds);
		foreach ($orders as $key => $order) {
			$authorId = $order['author_id'];
			if (!empty($users[$authorId])) {
				$orders[$key]['author'] = $users[$authorId];
			}
			else {
				throw new \Exception('Failed to find author for order %1!', $order['id']);
			}
		}
	}

	return $orders;
}

function getOrdersCount($where) {
	return \Mysql\count(_getConnect(), _getTable(), $where);
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
		throw new \Exception('Задача у заказа не может быть пустой', 800);
	}

	$description = trim($description);
	if (!$description) {
		throw new \Exception('Описание заказа не может быть пустым', 800);
	}

	$price = intval($price);
	if (!$price) {
		throw new \Exception('Необходимо указать стоимость работы', 800);
	}
	if ($price < 100) {
		throw new \Exception('Стоимость должна быть больше 100 рублей', 800);
	}
	if ($price > 100000000) {
		throw new \Exception('Стоимость должна быть меньше 100000000 рублей', 800);
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