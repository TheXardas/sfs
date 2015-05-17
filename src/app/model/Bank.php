<?
namespace Bank;


function getPaymentFrom($user, $price) {
	if ($user['money'] < $price) {
		// TODO вероятно поприличнее будет эту ошибку все-таки не показывать.
		// TODO и еще стоит не отображать заказы, когда у заказчиков денег нет, но это субъективно.
		throw new \Exception('У заказчика недостаточно средств!', 803);
	}

	$newUserMoney = $user['money'] - $price;
	$result = \User\setMoney($user['id'], $newUserMoney);
	if (!$result) {
		throw new \Exception('Failed updating customer money!');
	}
	return $result;
}

function payTo($user, $money) {
	$newUserMoney = $user['money'] + $money;
	$result = \User\setMoney($user['id'], $newUserMoney);
	if (!$result) {
		throw new \Exception('Failed paying to user!');
	}
	return $result;
}

function orderBilling(array $order) {
	$order['price'];
	$order['executor_id'];
	$order['author_id'];
	$systemUser = \User\getSystemUser();
	$systemUser['id'];
}

function getSystemCommission() {
	// 30%
	return 0.3;
}
