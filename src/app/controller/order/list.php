<?
require_once MODEL_DIR.'/Order.php';

$currentUser = \Session\getCurrentUser();
$orders = \Order\getActiveOrders($currentUser);

return [
	'ctx' => [
		'orders' => $orders,
	],
];