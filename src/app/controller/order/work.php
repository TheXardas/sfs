<?
require_once MODEL_DIR.'/Order.php';

list($orderId) = \Controller\filterParams([
	'orderId' => ['integer', 0],
], $params);

$currentUser = \Session\getCurrentUser();
\Order\work($orderId, $currentUser);

$currentUser = \Session\getCurrentUser(true);

return [
	'ctx' => [
		'result' => 1,
		'userAccount' => $currentUser['money'],
	],
];