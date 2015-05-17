<?
require_once MODEL_DIR.'/Order.php';

list($subject, $description, $price) = \Controller\filterParams([
	'subject' => ['string', ''],
	'description' => ['string', ''],
	'price' => ['int', 0],
], $params);

$result = \Order\create($subject, $description, $price);

\Controller\redirect('/');