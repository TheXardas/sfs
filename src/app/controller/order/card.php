<?
list($order) = \Controller\filterParams([
	'order' => ['array', null]
], $params);

if (!$order || empty($order['subject']) || empty($order['description'])) {
	throw new Exception('Required order param is missing');
}

$subject = $order['subject'];
$description = \View\esc($order['description']);
$price = $order['price'];
$time_created = date('Y-m-d H:i:s', $order['time_created']);
$author = $order['author'];

return [
	'ctx' => [
		'subject' => $subject,
		'description' => $description,
		'price' => $price,
		'time_created' => $time_created,
		'author' => $author,
	],
];