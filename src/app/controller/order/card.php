<?
list($order) = \Controller\filterParams([
	'order' => ['array', null]
], $params);

if (!$order || empty($order['subject']) || empty($order['description'])) {
	throw new Exception('Required order param is missing');
}

return [
	'ctx' => [
		'order_id' => $order['id'],
		'subject' => $order['subject'],
		'description' => \View\esc($order['description']),
		'price' =>  $order['price'],
		'time_created' => date('Y-m-d H:i:s', $order['time_created']),
		'author' => $order['author'],
	],
];