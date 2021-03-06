<?
namespace Order;
require_once LIB_DIR.'/DbManager.php';
require_once LIB_DIR.'/Mysql.php';
require_once MODEL_DIR.'/User.php';
require_once MODEL_DIR.'/Bank.php';
require_once LIB_DIR.'/DbTransaction.php';

define('ORDER_LIST_LIMIT', 5);

/**
 * Получить заказ по его ID.
 *
 * @param $id
 * @param $forUpdate
 *
 * @return mixed
 * @throws \Exception
 */
function get($id, $forUpdate = false) {
	if (!$id) {
		throw new \Exception('ID required!');
	}
	return \Mysql\selectOne(_getConnect(), _getTable(), _getColumnList(), ['id' => $id], [], $forUpdate);
}

/**
 * Выполнить работу по заказу
 *
 * @param int $orderId — Заказ
 * @param int $executorId — Исполнитель заказа (пока только текущей пользователь)
 * @param int $authorId — Id-автора заказа. Параметр сюда прокидывается исключительно для оптимизации.
 *
 * @throws \Exception
 */
function work($orderId, $executorId, $authorId) {
	if (!$authorId || !$orderId || !$executorId) {
		throw new \Exception('All params are required!');
	}
	if ($executorId !== \Session\getCurrentUserId()) {
		throw new \Exception('Only current users can be executors');
	}

	$dbs = [
		\Order\_getDbName(),
		\User\_getDbName(),
		\Transaction\_getDbName(),
	];
	$transactionId = \DbTransaction\beginTransaction($dbs);

	try {
		// Во избежание конкуретной работы со счетами наших пользователей, надо произвести залочку for update.
		// Лочим пользователей, которым мы будем обновлять счет.
		// Таким образом гарантируется отсутствие дедлоков и конкуретных изменений.
		// При этом мы не блокируем ничего лишнего.
		// TODO вероятно это можно сделать еще быстрее
		$lockedUserIds = [$executorId, $authorId];
		$users = \User\getByIds($lockedUserIds, true);
		if (empty($users[$executorId])) {
			throw new \Exception('Failed to find executor!');
		}
		$executor = $users[$executorId];
		if (empty($users[$authorId])) {
			throw new \Exception('Failed to find author!');
		}
		$customer = $users[$authorId];

		if (!\User\canWorkOnOrders($executor)) {
			throw new \Exception('Только исполнители могут работать над заказами.', 802);
		}

		$order = get($orderId, true);
		if (!$order) {
			throw new \Exception('No order with such id exist!');
		}

		// Обязательно надо не забыть проверить автора!
		if ((int)$order['author_id'] !== $authorId) {
			// TODO для разбирательств возможно стоит добавить id заказа и автора
			throw new \Exception('Invalid author id param! It differs from one in order!');
		}

		if ($order['is_finished']) {
			throw new \Exception('Этот заказ уже завершен.', 801);
		}

		// Завершаем заказ
		$result = finishOrder($orderId, $executorId);
		if (!$result) {
			throw new \Exception('Failed finishing order!');
		}

		// Вычитаем со счета клиента
		\Bank\getPaymentFrom($customer, $order['price'], $orderId, GET_PAYMENT_FOR_WORK_OPERATION);

		// Платим работнику гонорар
		$commission = round(\Bank\getSystemCommission() * $order['price']);
		$executorPayment = $order['price'] - $commission;
		\Bank\payTo($executor, $executorPayment, $orderId, PAY_FOR_WORK_OPERATION);

		// Пишем на счет системы комиссию
		// Системного пользователя достаем самым последним, для оптимизации.
		// Он используется во всех транзакциях по работе над заказами, поэтому его надо доставать в последнюю очередь.
		// Таким образом мы минимизируем время, когда реально системный пользователь залочен.
		$systemUser = \User\getSystemUser(true);
		\Bank\payTo($systemUser, $commission, $orderId, SYSTEM_COMMISSION_OPERATION);

		\DbTransaction\commit($dbs, $transactionId);
	}
	catch (\Exception $e) {
		\DbTransaction\rollback($dbs, $transactionId);
		throw $e;
	}
}

/**
 * Завершаем заказ
 *
 * @param $orderId
 * @param $executorId
 *
 * @return int
 * @throws \Exception
 */
function finishOrder($orderId, $executorId) {
	return \Mysql\update(_getConnect(), _getTable(), [
		'is_finished' => 1,
		'executor_id' => $executorId,
		'time_finished' => time(),
	], [
		'id' => $orderId,
	]);
}

/**
 * Возвращает активные заказы для пользователя
 *
 * @param $currentUser
 * @param null $offset
 *
 * @return array
 */
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

/**
 * Возвращает количество активных заказов для пользователя
 *
 * @todo как-нибудь интегрировать в getActiveOrders, что ли
 * @param $currentUser
 *
 * @return int|mixed
 */
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

/**
 * Возвращает активные заказы для исполнителя
 *
 * @param $executorId
 * @param null $offset
 *
 * @return array
 * @throws \Exception
 */
function getActiveOrdersForExecutor($executorId, $offset = null) {
	$where = ['is_finished' => 0];
	$orderBy = ['time_created' => 'DESC'];
	return getOrders($where, $orderBy, ORDER_LIST_LIMIT, $offset);
}

/**
 * Считает активные заказы для исполнителя
 *
 * @param $executorId
 *
 * @return int|mixed
 */
function getActiveOrdersCountForExecutor($executorId) {
	$where = ['is_finished' => 0];
	return getOrdersCount($where);
}

/**
 * Возвращает активные заказы для заказчика
 *
 * @param $customerId
 * @param null $offset
 *
 * @return array
 * @throws \Exception
 */
function getActiveOrdersForCustomer($customerId, $offset = null) {
	$where = ['is_finished' => 0, 'author_id' => $customerId];
	$orderBy = ['time_created' => 'DESC'];
	return getOrders($where, $orderBy, ORDER_LIST_LIMIT, $offset);
}

/**
 * Считает активные заказы для заказчика
 *
 * @param $customerId
 *
 * @return int|mixed
 */
function getActiveOrdersCountForCustomer($customerId) {
	$where = ['is_finished' => 0, 'author_id' => $customerId];
	return getOrdersCount($where);
}

/**
 * Достает из базы заказы
 * После чего добавляет массив 'author', с параметрами автора заказа
 *
 * @param $where
 * @param null $orderBy
 * @param null $limit
 * @param null $offset
 *
 * @return array
 * @throws \Exception
 */
function getOrders($where, $orderBy = null, $limit = null, $offset = null) {
	$orders = \Mysql\select(_getConnect(), _getTable(), _getColumnList(), $where, $orderBy, $limit, $offset);
	if ($orders) {
		// Филлим авторов заказов
		$userIds = [];
		// TODO use array_column()
		foreach ($orders as $order) {
			$userIds[] = $order['author_id'];
		}

		$users = \User\getByIds($userIds);
		foreach ($orders as $key => $order) {
			$authorId = $order['author_id'];
			if (!empty($users[$authorId])) {
				$orders[$key]['author'] = $users[$authorId];
			}
			else {
				throw new \Exception('Failed to find author for order %1s!', $order['id']);
			}
		}
	}

	return $orders;
}

/**
 * Посчитать заказы
 *
 * @param $where
 *
 * @return int|mixed
 */
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
		$author_id = \Session\getCurrentUserId();
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
	return 'orders';
}

/**
 * Возвращает список колонок, которые можно выбирать из бд для данной модели
 *
 * @return array
 */
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