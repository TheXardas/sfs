<?
require_once MODEL_DIR.'/Order.php';

list($offset) = \Controller\filterParams([
	'offset' => ['integer', null]
], $params);

$currentUser = \Session\getCurrentUser();
$orders = \Order\getActiveOrders($currentUser, $offset);
$all = \Order\getActiveOrdersCount($currentUser);
$current = $offset + count($orders);

return [
	'ctx' => [
		'orders' => $orders,
		'count' => $all,
		'showPager' => $current < $all,
		'hideHeader' => (bool) $offset,
	],
];