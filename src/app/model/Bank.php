<?
namespace Bank;

require_once MODEL_DIR.'/Transaction.php';

/**
 * Списать со счета
 *
 * @param $user
 * @param $price
 * @param null $orderId
 * @param int $operation
 *
 * @return int
 * @throws \Exception
 */
function getPaymentFrom($user, $price, $orderId = null, $operation = GET_PAYMENT_FROM_OPERATION) {
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
	\Transaction\create($user['id'], $operation, $price, $orderId);

	return $result;
}

/**
 * Записать на счет
 *
 * @param $user
 * @param $money
 * @param null $orderId
 * @param int $operation
 *
 * @return int
 * @throws \Exception
 */
function payTo($user, $money, $orderId = null, $operation = PAY_TO_OPERATION) {
	$newUserMoney = $user['money'] + $money;
	$result = \User\setMoney($user['id'], $newUserMoney);
	if (!$result) {
		throw new \Exception('Failed paying to user!');
	}
	\Transaction\create($user['id'], $operation, $money, $orderId);
	return $result;
}

/**
 * Возвращает модификатор комиссии системы в виде дробного числа
 *
 * @return float
 */
function getSystemCommission() {
	// 30%
	return 0.3;
}
