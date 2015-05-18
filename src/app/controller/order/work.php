<?
require_once MODEL_DIR.'/Order.php';

list($orderId, $authorId) = \Controller\filterParams([
	'orderId' => ['integer', 0],
	// authorId - для оптимизации. Потом надо обязательно проверить его соответствие тому, который в заказе.
	'authorId' => ['integer', 0],
], $params);

$currentUserId = \Session\getCurrentUserId();
\Order\work($orderId, $currentUserId, $authorId);

$currentUser = \Session\getCurrentUser(true);

return [
	'ctx' => [
		'result' => 1,
		'userAccount' => $currentUser['money'],
	],
];